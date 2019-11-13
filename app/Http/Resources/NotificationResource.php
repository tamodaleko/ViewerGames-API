<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class NotificationResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'notified_user_id' => $this->notified_user_id,
            'notified_user' => $this->user ? $this->user->name : null,
            'notification_type_id' => $this->notification_type_id,
            'notification_type' => $this->type ? $this->type->name : null,
            'sub_type' => $this->sub_type,
            'message' => $this->message,
            'remove' => $this->remove,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null
        ];
    }
}
