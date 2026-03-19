<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // جلب الإشعارات غير المقروءة
    public function index(Request $request)
    {
        // unreadNotifications هي علاقة جاهزة في Laravel
        return response()->json([
            'notifications' => $request->user()->unreadNotifications
        ]);
    }

    // تعليم إشعار كمقروء
    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()
                                ->unreadNotifications()
                                ->where('id', $id)
                                ->first();

        if ($notification) {
            $notification->markAsRead();
            return response()->json(['message' => 'Notification marked as read.']);
        }

        return response()->json(['message' => 'Notification not found.'], 404);
    }
    
    // تعليم الكل كمقروء
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'All notifications marked as read.']);
    }
}