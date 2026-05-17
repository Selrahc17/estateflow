<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        $userId   = $authUser->id;
        $search   = $request->input('search');

        $query = Message::where('from_user_id', $userId)
            ->orWhere('to_user_id', $userId);

        if ($search) {
            $query = Message::where(function ($q) use ($userId, $search) {
                $q->where('from_user_id', $userId)
                  ->orWhere('to_user_id', $userId);
            })->where(function ($q) use ($search) {
                $q->where('message', 'like', '%' . $search . '%')
                  ->orWhereHas('fromUser', fn($u) => $u->where('name', 'like', '%' . $search . '%'))
                  ->orWhereHas('toUser',   fn($u) => $u->where('name', 'like', '%' . $search . '%'));
            });
        }

        $conversations = $query
            ->with(['fromUser', 'toUser', 'reservation.property'])
            ->latest()
            ->get()
            ->groupBy(function ($msg) use ($userId) {
                return $msg->from_user_id === $userId ? $msg->to_user_id : $msg->from_user_id;
            })
            ->map(fn($msgs) => $msgs->first());

        $unreadCount = Message::where('to_user_id', $userId)->whereNull('read_at')->count();

        $view = $authUser->isClient() ? 'messages.index-client' : 'messages.index';
        return view($view, compact('conversations', 'unreadCount', 'search'));
    }

    public function show(User $user)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        $myId     = $authUser->id;

        Message::where('from_user_id', $user->id)
            ->where('to_user_id', $myId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = Message::where(function ($q) use ($myId, $user) {
                $q->where('from_user_id', $myId)->where('to_user_id', $user->id);
            })
            ->orWhere(function ($q) use ($myId, $user) {
                $q->where('from_user_id', $user->id)->where('to_user_id', $myId);
            })
            ->with(['fromUser', 'reservation.property'])
            ->oldest()
            ->get();

        $reservations = collect();
        if ($authUser->isClient()) {
            $client = \App\Models\Client::where('user_id', $myId)->first();
            if ($client) {
                $reservations = Reservation::where('client_id', $client->id)->with('property')->get();
            }
        } elseif ($authUser->isAgent()) {
            $agent = \App\Models\Agent::where('user_id', $myId)->first();
            if ($agent) {
                $reservations = Reservation::where('agent_id', $agent->id)->with(['property', 'client'])->get();
            }
        } else {
            $reservations = Reservation::with(['property', 'client'])->latest()->take(20)->get();
        }

        $view = $authUser->isClient() ? 'messages.show-client' : 'messages.show';
        return view($view, compact('user', 'messages', 'reservations'));
    }

    public function send(Request $request, User $user)
    {
        $request->validate([
            'message'        => 'nullable|string|max:2000',
            'reservation_id' => 'nullable|exists:reservations,id',
            'attachment'     => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,txt,zip',
        ]);

        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        $data = [
            'from_user_id'   => $authUser->id,
            'to_user_id'     => $user->id,
            'reservation_id' => $request->reservation_id ?: null,
            'message'        => $request->message ?? '',
        ];

        if ($request->hasFile('attachment')) {
            $file                    = $request->file('attachment');
            $path                    = $file->store('chat-attachments', 'public');
            $mime                    = $file->getMimeType();
            $data['attachment']      = $path;
            $data['attachment_type'] = str_starts_with($mime, 'image/') ? 'image' : 'file';
        }

        $msg = Message::create($data);
        $msg->load('reservation.property');

        return response()->json([
            'id'              => $msg->id,
            'mine'            => true,
            'message'         => e($msg->message),
            'time'            => $msg->created_at->format('M d, h:i A'),
            'property'        => $msg->reservation?->property?->title,
            'read_at'         => null,
            'attachment'      => $msg->attachment ? asset('storage/' . $msg->attachment) : null,
            'attachment_type' => $msg->attachment_type,
            'attachment_name' => $msg->attachment ? basename($msg->attachment) : null,
        ]);
    }

    public function typing(User $user)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        Cache::put('typing_' . $authUser->id . '_to_' . $user->id, true, 5);
        return response()->json(['ok' => true]);
    }

    public function isTyping(User $user)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        $isTyping = Cache::has('typing_' . $user->id . '_to_' . $authUser->id);
        return response()->json(['typing' => $isTyping]);
    }

    public function poll(Request $request, User $user)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        $myId     = $authUser->id;
        $afterId  = $request->input('after', 0);

        $messages = Message::where(function ($q) use ($myId, $user) {
                $q->where('from_user_id', $user->id)->where('to_user_id', $myId);
            })
            ->where('id', '>', $afterId)
            ->with(['fromUser', 'reservation.property'])
            ->oldest()
            ->get()
            ->map(fn($msg) => [
                'id'              => $msg->id,
                'mine'            => false,
                'message'         => e($msg->message),
                'time'            => $msg->created_at->format('M d, h:i A'),
                'property'        => $msg->reservation?->property?->title,
                'read_at'         => $msg->read_at?->format('M d, h:i A'),
                'attachment'      => $msg->attachment ? asset('storage/' . $msg->attachment) : null,
                'attachment_type' => $msg->attachment_type,
                'attachment_name' => $msg->attachment ? basename($msg->attachment) : null,
            ]);

        Message::where('from_user_id', $user->id)
            ->where('to_user_id', $myId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json($messages);
    }

    public function create(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->isClient()) {
            $contacts = User::where('role', 'agent')->where('is_active', true)->get();
        } elseif ($user->isAgent()) {
            $contacts = User::where('role', 'client')->where('is_active', true)->get();
        } else {
            $contacts = User::whereIn('role', ['client', 'agent'])->where('is_active', true)->get();
        }

        $selectedUserId = $request->to;

        $view = $user->isClient() ? 'messages.create-client' : 'messages.create';
        return view($view, compact('contacts', 'selectedUserId'));
    }
}
