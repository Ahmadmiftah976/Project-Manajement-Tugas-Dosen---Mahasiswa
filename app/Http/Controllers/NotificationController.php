<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // Show all notifications
    public function index()
    {
        $notifications = Auth::user()->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $unreadCount = Auth::user()->unreadNotifications()->count();
        
        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    // Mark notification as read
    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
        
        $notification->update(['is_read' => true]);
        
        // Redirect to link if provided
        if ($notification->link) {
            return redirect($notification->link);
        }
        
        return back()->with('success', 'Notifikasi ditandai sebagai sudah dibaca');
    }

    // Mark all notifications as read
    public function markAllAsRead()
    {
        Auth::user()->notifications()
            ->where('is_read', false)
            ->update(['is_read' => true]);
        
        return back()->with('success', 'Semua notifikasi ditandai sebagai sudah dibaca');
    }

    // Delete notification
    public function destroy($id)
    {
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
        
        $notification->delete();
        
        return back()->with('success', 'Notifikasi berhasil dihapus');
    }

    // Delete all read notifications
    public function deleteAllRead()
    {
        Auth::user()->notifications()
            ->where('is_read', true)
            ->delete();
        
        return back()->with('success', 'Semua notifikasi yang sudah dibaca berhasil dihapus');
    }

    // Get unread count (AJAX)
    public function getUnreadCount()
    {
        $count = Auth::user()->unreadNotifications()->count();
        
        return response()->json(['count' => $count]);
    }

    // Get recent notifications (AJAX)
    public function getRecent()
    {
        $notifications = Auth::user()->notifications()
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
        
        return response()->json($notifications);
    }

    // Get notifications by type
    public function getByType($type)
    {
        $validTypes = ['deadline', 'submission', 'comment', 'grade'];
        
        if (!in_array($type, $validTypes)) {
            abort(404);
        }
        
        $notifications = Auth::user()->notifications()
            ->where('type', $type)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('notifications.by-type', compact('notifications', 'type'));
    }
}