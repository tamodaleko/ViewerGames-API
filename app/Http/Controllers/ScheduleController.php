<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserScheduleResourceCollection;
use App\Models\User\User;
use App\Models\User\UserSchedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => ['allForUnAuthenticated']
        ]);
    }

    /**
     * Show user's schedules.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\UserScheduleResourceCollection
     */
    public function all(Request $request)
    {
        if (!$request->user()->schedules->count()) {
            return $this->errorResponse('User has no schedules created.');
        }

        return $this->wrapResponse(new UserScheduleResourceCollection($request->user()->schedules));
    }

    /**
     * Store user's schedule.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\UserScheduleResourceCollection
     */
    public function store(Request $request)
    {
        $request->validate(UserSchedule::$rules);

        $schedule = new UserSchedule;

        $schedule->user_id = $request->user()->id;
        $schedule->day = $request->day;
        $schedule->start_time = $request->start_time;

        if (!$schedule->save()) {
            return $this->errorResponse('User\'s schedule could not be created.', 500);
        }

        return $this->wrapResponse(new UserScheduleResourceCollection($request->user()->schedules()->get()));
    }

    /**
     * Submit user's schedule form.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\UserScheduleResourceCollection
     */
    public function form(Request $request)
    {
        $scheduleType = $request->schedule_type;

        if (!$scheduleType || !in_array($scheduleType, ['random', 'scheduled', 'both'])) {
            return $this->errorResponse('Invalid param \'schedule_type\'.', 500);
        }

        $request->user()->clearSchedules();

        if ($scheduleType !== 'random') {
            for ($i = 1; $i <= 7; $i++) {
                $day = 'day_' . $i;

                if (isset($request->$day)) {
                    $request->user()->addSchedule($i, $request->$day);
                }
            }
        }

        if (!$request->user()->updateSetting('schedule_type', $scheduleType)) {
            return $this->errorResponse('User\'s schedules could not be saved.', 500);
        }

        return $this->wrapResponse(new UserScheduleResourceCollection($request->user()->schedules()->get()));
    }

    /**
     * Show schedules for specified unauthenticated user.
     *
     * @param \App\Models\User\User $user
     * @return \App\Http\Resources\UserScheduleResourceCollection
     */
    public function allForUnAuthenticated(User $user)
    {
        if (!$user->schedules->count()) {
            return $this->errorResponse('User has no schedules created.');
        }

        return $this->wrapResponse(new UserScheduleResourceCollection($user->schedules));
    }
}
