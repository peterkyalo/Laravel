<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Message\SendMessageRequest;
use App\Http\Resources\V1\MessageResource;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MessageController extends Controller
{
    /**
     * List user received messages (Inbox).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $messages = Message::where('recipient_id', $request->user()->id)
            ->with(['sender', 'recipient'])
            ->latest()
            ->paginate(20);

        return MessageResource::collection($messages);
    }

    /**
     * List user sent messages.
     */
    public function sent(Request $request): AnonymousResourceCollection
    {
        $messages = Message::where('sender_id', $request->user()->id)
            ->with(['sender', 'recipient'])
            ->latest()
            ->paginate(20);

        return MessageResource::collection($messages);
    }

    /**
     * Send a new message.
     */
    public function store(SendMessageRequest $request): JsonResponse
    {
        $message = Message::create([
            'sender_id' => $request->user()->id,
            'recipient_id' => $request->recipient_id,
            'subject' => $request->subject,
            'body' => $request->body,
            'parent_id' => $request->parent_id,
        ]);

        return response()->json([
            'message' => 'Message sent successfully.',
            'data' => new MessageResource($message->load(['sender', 'recipient'])),
        ], 201);
    }

    /**
     * Read a message and mark as read.
     */
    public function show(Request $request, Message $message): JsonResponse
    {
        $user = $request->user();

        if ($message->recipient_id !== $user->id && $message->sender_id !== $user->id && ! $user->isAdmin()) {
            abort(403);
        }

        if ($message->recipient_id === $user->id && ! $message->read_at) {
            $message->update(['read_at' => now()]);
        }

        return response()->json([
            'message' => new MessageResource($message->load(['sender', 'recipient'])),
        ]);
    }
}
