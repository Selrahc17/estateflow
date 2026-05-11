<?php

namespace App\Providers;

use App\Models\Payment;
use App\Models\Property;
use App\Models\Reservation;
use App\Observers\PaymentObserver;
use App\Observers\PropertyObserver;
use App\Observers\ReservationObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Property::observe(PropertyObserver::class);
        Reservation::observe(ReservationObserver::class);
        Payment::observe(PaymentObserver::class);
    }
}
