<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function index()
    {
        return Inertia::render('Chat/Index');
    }

    public function users(Request $request)
    {
        return User::query()
            ->where('id', '!=', $request->user()->id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    public function messages(Request $request)
    {
        $user = $request->user();
        $afterId = $request->integer('after');

        $query = Message::query()->with('sender:id,name');

        if ($request->boolean('global')) {
            $query->whereNull('receiver_id');
        } elseif ($peerId = $request->integer('user_id')) {
            $query->where(function ($q) use ($user, $peerId) {
                $q->where('sender_id', $user->id)->where('receiver_id', $peerId);
            })->orWhere(function ($q) use ($user, $peerId) {
                $q->where('sender_id', $peerId)->where('receiver_id', $user->id);
            });
        } else {
            return response()->json(['error' => 'Missing global=1 or user_id'], 422);
        }

        if ($afterId) {
            $query->where('id', '>', $afterId);
        }

        return $query->orderBy('id')->limit(200)->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'body'        => ['required', 'string', 'max:2000'],
            'receiver_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $msg = Message::create([
            'sender_id'   => $request->user()->id,
            'receiver_id' => $data['receiver_id'] ?? null, // null => global
            'body'        => $data['body'],
        ]);

        return $msg->load('sender:id,name');
    }
}
