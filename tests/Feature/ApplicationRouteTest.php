<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApplicationRouteTest extends TestCase
{
    public function test_core_customer_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('home'));
        $this->assertTrue(Route::has('customer.products.index'));
        $this->assertTrue(Route::has('checkout.process'));
    }
}
