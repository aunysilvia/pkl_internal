<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransNotificationController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        Log::info('Midtrans Notification Received', $payload);

        $orderId           = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $paymentType       = $payload['payment_type'] ?? null;
        $statusCode        = $payload['status_code'] ?? null;
        $grossAmount       = $payload['gross_amount'] ?? null;
        $signatureKey      = $payload['signature_key'] ?? null;
        $fraudStatus       = $payload['fraud_status'] ?? null;
        $transactionId     = $payload['transaction_id'] ?? null;

        // 1. Validasi Field
        if (! $orderId || ! $transactionStatus || ! $signatureKey) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        // 2. Validasi Signature
        $serverKey         = config('midtrans.server_key');
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($signatureKey !== $expectedSignature) {
            Log::warning('Midtrans Notification: Invalid signature', ['order_id' => $orderId]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // 3. Cari Order & Payment
        $order = Order::where('order_number', $orderId)->first();
        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // 4. Idempotency Check (Cegah proses ulang jika sudah selesai)
        if (in_array($order->status, ['processing', 'shipped', 'delivered', 'cancelled'])) {
            return response()->json(['message' => 'Order already processed'], 200);
        }

        $payment = $order->payment;

        // 5. Update Payment Info
        if ($payment) {
            $payment->update([
                'midtrans_transaction_id' => $transactionId,
                'payment_type'            => $paymentType,
                'raw_response'            => json_encode($payload),
            ]);
        }

        // 6. Mapping Status
        switch ($transactionStatus) {
            case 'capture':
                if ($fraudStatus === 'challenge') {
                    $this->handlePending($order, $payment, 'Review Fraud');
                } else {
                    $this->handleSuccess($order, $payment);
                }
                break;

            case 'settlement':
                $this->handleSuccess($order, $payment);
                break;

            case 'pending':
                $this->handlePending($order, $payment);
                break;

            case 'deny':
            case 'expire':
            case 'cancel':
                $this->handleFailed($order, $payment, $transactionStatus);
                break;

            case 'refund':
            case 'partial_refund':
                $this->handleRefund($order, $payment);
                break;

            default:
                Log::info("Unknown status: $transactionStatus");
                break;
        }

        return response()->json(['message' => 'Notification processed'], 200);
    }

    protected function handleSuccess(Order $order, ?Payment $payment): void
    {
        DB::transaction(function () use ($order, $payment) {
            $order->update(['status' => 'processing']);
            $payment?->update([
                'status'  => 'success',
                'paid_at' => now(),
            ]);
        });
    }

    protected function handlePending(Order $order, ?Payment $payment, string $msg = ''): void
    {
        $payment?->update(['status' => 'pending']);
    }

    protected function handleFailed(Order $order, ?Payment $payment, string $reason): void
    {
        DB::transaction(function () use ($order, $payment) {
            $order->update(['status' => 'cancelled']);
            $payment?->update(['status' => 'failed']);

            // Restock Logic - Hanya jika sebelumnya belum dicancel
            foreach ($order->items as $item) {
                $item->product?->increment('stock', $item->quantity);
            }
        });
    }

    protected function handleRefund(Order $order, ?Payment $payment): void
    {
        $payment?->update(['status' => 'refunded']);
    }
}
