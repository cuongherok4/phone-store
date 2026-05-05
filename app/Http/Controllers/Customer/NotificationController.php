<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Danh sách thông báo.
     */
    public function index()
    {
        $notifications = $this->notificationService->getNotifications(20);
        return view('customer.notifications.index', compact('notifications'));
    }

    /**
     * Đánh dấu đã đọc.
     */
    public function markAsRead($id)
    {
        $this->notificationService->markAsRead($id);
        return back();
    }

    /**
     * Đánh dấu tất cả đã đọc.
     */
    public function markAllAsRead()
    {
        $this->notificationService->markAllAsRead();
        return back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
    }
}
