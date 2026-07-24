<?php

namespace Tests\Unit\Mail;

use App\Mail\OrderConfirmation;
use App\Mail\OrderStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Tests\TestCase;

class OrderMailQueueTest extends TestCase
{
    public function test_order_mails_are_queueable(): void
    {
        $this->assertContains(ShouldQueue::class, class_implements(OrderConfirmation::class));
        $this->assertContains(ShouldQueue::class, class_implements(OrderStatusChanged::class));
    }
}
