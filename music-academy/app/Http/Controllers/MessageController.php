<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $messages = Message::where('recipient_id', $user->id)
            ->whereNull('parent_id')
            ->with(['sender', 'replies' => fn ($q) => $q->latest()])
            ->latest()
            ->paginate(15);

        $unreadCount = Message::where('recipient_id', $user->id)->whereNull('read_at')->count();

        return view('messages.index', compact('messages', 'unreadCount'));
    }

    public function sent()
    {
        $user = auth()->user();

        $messages = Message::where('sender_id', $user->id)
            ->whereNull('parent_id')
            ->with('recipient')
            ->latest()
            ->paginate(15);

        return view('messages.sent', compact('messages'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();

        // Allowed recipients: admin can message anyone; instructors can message students/admins; students can message instructors/admins
        $recipients = User::where('id', '!=', $user->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedRecipientId = $request->query('to');

        return view('messages.create', compact('recipients', 'selectedRecipientId'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'recipient_id' => ['required', 'exists:users,id'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        abort_if((int)$validated['recipient_id'] === $user->id, 422, 'Cannot message yourself.');

        $message = Message::create([
            'sender_id' => $user->id,
            'recipient_id' => $validated['recipient_id'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
        ]);

        return redirect()->route('messages.show', $message)->with('success', 'Message sent.');
    }

    public function show(Message $message)
    {
        $user = auth()->user();
        abort_unless($message->sender_id === $user->id || $message->recipient_id === $user->id, 403);

        // If child message, redirect to parent thread
        if ($message->parent_id) {
            return redirect()->route('messages.show', $message->parent_id);
        }

        // Mark unread messages in this thread as read
        if ($message->recipient_id === $user->id && is_null($message->read_at)) {
            $message->update(['read_at' => now()]);
        }
        $message->replies()->where('recipient_id', $user->id)->whereNull('read_at')->update(['read_at' => now()]);

        $message->load(['sender', 'recipient', 'replies.sender']);

        return view('messages.show', compact('message'));
    }

    public function reply(Request $request, Message $message)
    {
        $user = auth()->user();
        abort_unless($message->sender_id === $user->id || $message->recipient_id === $user->id, 403);

        $rootMessage = $message->parent_id ? $message->parent : $message;

        $validated = $request->validate([
            'body' => ['required', 'string'],
        ]);

        $recipientId = ($rootMessage->sender_id === $user->id)
            ? $rootMessage->recipient_id
            : $rootMessage->sender_id;

        $rootMessage->replies()->create([
            'sender_id' => $user->id,
            'recipient_id' => $recipientId,
            'subject' => 'Re: '.$rootMessage->subject,
            'body' => $validated['body'],
        ]);

        return redirect()->route('messages.show', $rootMessage)->with('success', 'Reply sent.');
    }
}
