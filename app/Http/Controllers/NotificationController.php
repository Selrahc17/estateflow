<?php

namespace App\Http\Controllers;

use App\Models\EstateNotification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = EstateNotification::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('status')) {
            $query->where('is_read', $request->status === 'read');
        }

        $notifications = $query->latest()->paginate(15)->withQueryString();
        $totalCount    = EstateNotification::count();
        $unreadCount   = EstateNotification::unread()->count();
        $readCount     = EstateNotification::where('is_read', true)->count();

        return view('notifications.index', compact('notifications', 'totalCount', 'unreadCount', 'readCount'));
    }

    public function create()
    {
        $users = User::where('is_active', true)->get();
        return view('notifications.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'             => 'required|string|max:100',
            'message'          => 'required|string',
            'priority'         => 'required|in:low,normal,high,urgent',
            'notifiable_type'  => 'required|in:user',
            'notifiable_id'    => 'required|exists:users,id',
        ]);

        EstateNotification::create([
            'type'            => $request->type,
            'data'            => ['message' => $request->message, 'title' => $request->type],
            'priority'        => $request->priority,
            'notifiable_type' => User::class,
            'notifiable_id'   => $request->notifiable_id,
            'is_read'         => false,
        ]);

        return redirect()->route('notifications.index')->with('success', 'Notification sent successfully.');
    }

    public function show(EstateNotification $notification)
    {
        if (!$notification->is_read) {
            $notification->markAsRead();
        }
        return view('notifications.show', compact('notification'));
    }

    public function markRead(EstateNotification $notification)
    {
        $notification->markAsRead();
        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead()
    {
        EstateNotification::unread()->update(['is_read' => true, 'read_at' => now()]);
        return back()->with('success', 'All notifications marked as read.');
    }

    public function destroy(EstateNotification $notification)
    {
        $notification->delete();
        return back()->with('success', 'Notification deleted.');
    }
}
