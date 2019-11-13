<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResourceCollection;
use App\Models\Notification\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Show user's notifications.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\NotificationResourceCollection
     */
    public function all(Request $request)
    {
        $notifications = $request->user()->notifications()->where('remove', 0)->get();
        
        if (!count($notifications)) {
            return $this->errorResponse('User has no notifications created.');
        }

        return $this->wrapResponse(new NotificationResourceCollection($notifications));
    }

    /**
     * Remove user's notification.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Notification\Notification $notification
     * @return \Illuminate\Http\Response
     */
    public function remove(Request $request, Notification $notification)
    {
        if ($request->user()->id !== $notification->notified_user_id) {
            return $this->errorResponse('Access denied.', 401);
        }

        $notification->remove = 1;

        if (!$notification->save()) {
            return $this->errorResponse('Notification could not be removed.', 500);
        }

        return $this->successResponse('Notification has been removed successfully.');
    }
}
