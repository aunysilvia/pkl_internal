<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Auth\Events\Login;
use App\Listeners\MergeCartListener;


class MergeCartListener
{
    protected $listen = [
    Login::class => [
        MergeCartListener::class,
    ],
];

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
       $cartService = new \App\Services\CartService();
       $cartService->mergeCartOnLogin();

    }
}
