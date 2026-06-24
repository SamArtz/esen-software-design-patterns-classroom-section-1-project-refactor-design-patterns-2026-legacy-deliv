<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Services\Payments\BacTransferAdapter;
use App\Services\Payments\BacTransferHandler;
use App\Services\Payments\CashAdapter;
use App\Services\Payments\N1coAdapter;
use App\Services\Payments\N1coHandler;
use App\Services\Payments\Contracts\PaymentGatewayAdapter;
use App\Services\Payments\WompiAdapter;
use App\Services\Payments\WompiHandler;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */

    public function register(): void
    {
        $this->app->bind(PaymentGatewayAdapter::class, function () {
            return match (config('services.payment.default', 'wompi')) {
                'wompi' => new WompiAdapter(new WompiHandler()),
                'n1co' => new N1coAdapter(new N1coHandler()),
                'bac_transfer' => new BacTransferAdapter(new BacTransferHandler()),
                'cash' => new CashAdapter(),

                default => new WompiAdapter(new WompiHandler()),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            OrderStatusChanged::class,
            HandleOrderStatusActions::class,
        );
    }

    
}
