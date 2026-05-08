<?php

namespace App\Http\Controllers\Staff;

use App\Models\Staff;
use App\Models\StaffMessage;
use App\Models\StaffMessageReply;
use Illuminate\Http\Request;
use App\Http\Controllers\Base\BaseServiceController;
use App\Models\Admin;

class MessageController extends BaseServiceController
{
    public function index(Request $request)
    {
        $staffId = auth('staff')->id();

        $messages = StaffMessage::query()

            // Staff can see only their conversations
            ->where(function ($q) use ($staffId) {
                $q->where(function ($q2) use ($staffId) {
                    $q2->where('sender_type', 'staff')
                        ->where('sender_id', $staffId);
                })
                    ->orWhere(function ($q2) use ($staffId) {
                        $q2->where('receiver_type', 'staff')
                            ->where('receiver_id', $staffId);
                    });
            });

        /*
        |------------------------------------------------------------------
        | Filters
        |------------------------------------------------------------------
        */

        if ($request->date) {
            $messages->whereDate('created_at', $request->date);
        }

        /*
        |------------------------------------------------------------------
        | Unread replies count (admin replies unread for staff)
        |------------------------------------------------------------------
        */

        $messages->withCount([
            'replies as unread_replies_count' => function ($q) {
                $q->where('is_read', false)
                    ->where('sender_type', 'admin');
            }
        ]);

        $messages = $messages->latest()->paginate(utility('pagination', 50));

        return view('staff.messages.index', compact('messages'));
    }

    public function create()
    {
        // Check which guard is logged in
        if (auth('staff')->check()) {

            $authUser = auth('staff')->user();

            // Staff can send to all admins
            $users = Admin::orderBy('name', 'asc')->get();

        } else {
            abort(403, 'Unauthorized access');
        }

        return view('staff.messages.create', compact('users'));
    }

    public function store(Request $request)
    {

        $data = $request->validate([
            'receiver_id' => 'required|integer',
            'subject' => 'required',
            'message' => 'required'
        ]);

        if ($this->isStaff()) {
            $data['sender_type'] = 'staff';
            $data['sender_id'] = $this->currentStaff()->id;
            $data['receiver_type'] = 'admin';
        }

        StaffMessage::create($data);

        return back()->with('success', 'Message sent successfully');
    }


    public function show($id)
    {
        $conversation = StaffMessage::findOrFail(decrypt($id));

        // Mark main message as read (if admin is receiver)
        if ($conversation->receiver_type === 'staff') {
            $conversation->update(['is_read' => true]);
        }

        // Mark all admin replies as read
        StaffMessageReply::where('staff_message_id', $conversation->id)
            ->where('sender_type', 'admin')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $replies = $conversation->replies()
            ->orderBy('created_at')
            ->get();

        return view('staff.messages.show', compact('conversation', 'replies'));
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $conversation = StaffMessage::findOrFail(decrypt($id));

        StaffMessageReply::create([
            'staff_message_id' => $conversation->id,
            'sender_type' => 'staff',
            'sender_id' => auth('staff')->id(),
            'message' => $request->message,
            'is_read' => false,
        ]);

        return back();
    }

    public function getUnreadCount()
    {
        $userType = 'staff';
        $userId = auth('staff')->id();

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ Unread Main Messages
        |--------------------------------------------------------------------------
        */
        $unreadMain = StaffMessage::where('receiver_type', $userType)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ Get Conversations Belonging To Current User
        |--------------------------------------------------------------------------
        */
        $conversationIds = StaffMessage::where(function ($q) use ($userType, $userId) {
            $q->where('receiver_type', $userType)
                ->where('receiver_id', $userId);
        })
            ->orWhere(function ($q) use ($userType, $userId) {
                $q->where('sender_type', $userType)
                    ->where('sender_id', $userId);
            })
            ->pluck('id');

        /*
        |--------------------------------------------------------------------------
        | 3️⃣ Unread Replies From Opposite Role
        |--------------------------------------------------------------------------
        */
        $unreadReplies = StaffMessageReply::whereIn('staff_message_id', $conversationIds)
            ->where('sender_type', '!=', $userType)
            ->where('is_read', false)
            ->count();

        return $unreadMain + $unreadReplies;
    }
}
