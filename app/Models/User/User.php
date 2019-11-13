<?php

namespace App\Models\User;

use App\Models\Match;
use App\Models\Player\PlayerCard;
use App\Models\Queue\Queue;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use Zizaco\Entrust\Traits\EntrustUserTrait;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, EntrustUserTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Get the social record associated with the user.
     */
    public function social()
    {
        return $this->hasOne('App\Models\Social');
    }

    /**
     * Get the settings for the user.
     */
    public function settings()
    {
        return $this->hasMany('App\Models\User\UserSetting');
    }

    /**
     * Get the queues for the user.
     */
    public function queues()
    {
        return $this->hasMany('App\Models\Queue\Queue')->orderBy('created_at', 'desc');
    }

    /**
     * Get the schedules for the user.
     */
    public function schedules()
    {
        return $this->hasMany('App\Models\User\UserSchedule');
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications()
    {
        return $this->hasMany('App\Models\Notification\Notification', 'notified_user_id');
    }

    /**
     * Get the player cards for the user.
     */
    public function playerCards()
    {
        return $this->hasMany('App\Models\Player\PlayerCard');
    }

    /**
     * Get the badges for the user.
     */
    public function badges()
    {
        return $this->hasMany('App\Models\User\UserBadge', 'receiver_user_id');
    }

    /**
     * Get the badges for the user.
     */
    public function channelSubscriptions()
    {
        return $this->hasMany('App\Models\User\UserChannelSubscription');
    }

    /**
     * Get the settings record based on key.
     *
     * @param string $key
     * @return \App\Models\User\UserSetting
     */
    public function getSettingByKey($key)
    {
        return $this->settings->where('key', $key)->first();
    }

    /**
     * Update setting record or add new one.
     *
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function updateSetting($key, $value)
    {
        $setting = $this->getSettingByKey($key) ?: new UserSetting;

        $setting->user_id = $this->id;
        $setting->key = $key;
        $setting->value = $value;

        return $setting->save();
    }

    /**
     * Delete all schedules.
     *
     * @return void
     */
    public function clearSchedules()
    {
        foreach ($this->schedules as $schedule) {
            $schedule->delete();
        }
    }

    /**
     * Add schedule record.
     *
     * @param int $day
     * @param string $start_time
     * @return bool
     */
    public function addSchedule($day, $start_time)
    {
        $schedule = new UserSchedule;

        $schedule->user_id = $this->id;
        $schedule->day = $day;
        $schedule->start_time = $start_time;

        return $schedule->save();
    }

    /**
     * Get mvps selected by user.
     *
     * @param int $days
     * @return \App\Models\User\User
     */
    public function getUserMvps($days = 30)
    {
        $userIds = DB::table('matches')
            ->select('mvp')
            ->join('queues', 'queues.id', '=', 'matches.queue_id')
            ->where('user_id', $this->id)
            ->where('completed_at', '!=', null)
            ->where('mvp', '!=', null)
            ->orderBy('completed_at', 'desc');

        if (!empty($days)) {
            $dateBack = Carbon::now()->subDays($days)->format('Y-m-d 00:00:00');

            $userIds->where('completed_at', '>=', $dateBack);
        }

        return User::find($userIds->pluck('mvp')->toArray());
    }

    /**
     * Get completed matches for the user.
     *
     * @param bool $paginate
     * @param int $per_page
     * @return \App\Models\Match[]
     */
    public function getCompletedMatches($paginate = false, $per_page = 5)
    {
        $query = DB::table('matches')
            ->select('matches.id')
            ->join('queues', 'queues.id', '=', 'matches.queue_id')
            ->where('user_id', $this->id)
            ->where('completed_at', '!=', null)
            ->orderBy('completed_at', 'desc');

        $matches = Match::whereIn('id', $query->pluck('id')->toArray())->orderBy('completed_at', 'desc');

        if ($paginate) {
            return $matches->paginate($per_page);
        }

        return $matches->get();
    }

    /**
     * Get user's active queue.
     *
     * @return bool
     */
    public function getActiveQueue()
    {
        return $this->queues()->where('active', 1)->first();
    }

    /**
     * Get user's player card for active queue.
     *
     * @param \App\Models\Queue\Queue $queue
     * @return \App\Models\Player\PlayerCard
     */
    public function getActiveCard(Queue $queue)
    {
        $card = PlayerCard::where([
            ['user_id', $this->id],
            ['game_id', $queue->game_id],
            ['game_server_id', $queue->game_server_id]
        ]);

        if ($queue->game_rank_id) {
            $card->where('game_rank_id', $queue->game_rank_id);
        }

        return $card->first();
    }

    /**
     * Get streamer users.
     *
     * @return static[]
     */
    public static function getChannels()
    {
        return static::whereHas('roles', function ($query) {
            $query->where('roles.name', 'streamer');
        })->get();
    }
}
