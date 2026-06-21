<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Mark a specific notification as read and redirect to its URL.
     */
    public function markAsReadAndRedirect($id)
    {
        $notification = Auth::user()->notifications()->find($id);

        if ($notification) {
            $notification->markAsRead();
            
            $url = $notification->data['url'] ?? route('administrator.dashboard.index');
            return redirect($url);
        }

        return back()->with('error', 'Notifikasi tidak ditemukan.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        Auth::user()->unreadNotifications->markAsRead();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Semua notifikasi telah ditandai sudah dibaca.'
            ]);
        }

        return back()->with('success', 'Semua notifikasi telah ditandai sudah dibaca.');
    }
}
