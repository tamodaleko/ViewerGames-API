<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class UserScheduleResource extends Resource
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
            'day' => $this->day,
            'start_time' => $this->start_time
        ];
    }
}
