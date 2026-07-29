<?php

namespace Tests\Unit\Routing;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CheckoutRateLimitRouteTest extends TestCase
{
    public function test_checkout_process_route_has_rate_limit(): void
    {
        $route = Route::getRoutes()->getByName('checkout.process');

        $this->assertContains('throttle:checkout', $route->gatherMiddleware());
    }

    public function test_coupon_check_route_has_rate_limit(): void
    {
        $route = Route::getRoutes()->getByName('checkout.check_coupon');

        $this->assertContains('throttle:coupon-check', $route->gatherMiddleware());
    }
}
