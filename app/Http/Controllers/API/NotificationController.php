<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;
use App\Helpers\NotificationHelper;

class NotificationController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return $this->apiPaginatedResponse('Notifications fetched successfully.', $notifications);
    }

    public function show($id)
    {
        $notification = Notification::where('user_id', Auth::id())->find($id);
        if (!$notification) {
            return $this->apiError('Notification not found.', [], 404);
        }

        $notification->update(['is_read' => true]);
        return $this->apiResponse('Notification details fetched.', $notification);
    }

    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())->find($id);
        if (!$notification) {
            return $this->apiError('Notification not found.', [], 404);
        }

        $notification->update(['is_read' => true]);
        return $this->apiResponse('Notification marked as read.', $notification);
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())->update(['is_read' => true]);
        return $this->apiResponse('All notifications marked as read.');
    }

    public function destroy($id)
    {
        $notification = Notification::where('user_id', Auth::id())->find($id);
        if (!$notification) {
            return $this->apiError('Notification not found.', [], 404);
        }

        $notification->delete();
        return $this->apiResponse('Notification deleted successfully.');
    }

    public function clearAll()
    {
        Notification::where('user_id', Auth::id())->delete();
        return $this->apiResponse('All notifications cleared successfully.');
    }
    // NotificationHelper::sendNotification($userId, 'Title', 'Message', 'type', ['extra' => 'data']);


    public function testNotification()
    {
        NotificationHelper::sendFcmNotification(0, 'Test', 'Like Forum', 'forum_like', [
            'post_id' => 101,
            'liked_by' => 202,
            'user_id' => 303,
            'name' => 'John Doe',
            'role' => 'Admin',
            'profile_image' => 'https://example.com/images/john_doe.jpg'
        ]);
    }
}
