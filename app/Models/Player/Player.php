<?php

namespace App\Models\Player;

use App\Models\Match;
use App\Models\User\User;
use App\Models\User\UserChannelSubscription;
use Illuminate\Database\Eloquent\Model;
use TwitchApi;

class Player extends Model
{
    const STATUS_STREAMER = 'streamer';
    const STATUS_TICKET = 'ticket';
    const STATUS_SUBSCRIBER = 'subscriber';
    const STATUS_FOLLOWER = 'follower';
    const STATUS_VIEWER = 'viewer';
    const STATUS_GUEST = 'guest';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'match_id', 'team_id', 'game_role_id', 'in_game_name', 'status', 'ready', 'position'
    ];

    /**
     * Get the user record associated with the player.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User\User');
    }

    /**
     * Get the match record associated with the player.
     */
    public function match()
    {
        return $this->belongsTo('App\Models\Match');
    }

    /**
     * Get the team record associated with the player.
     */
    public function team()
    {
        return $this->belongsTo('App\Models\Team');
    }

    /**
     * Get the game role record associated with the player.
     */
    public function gameRole()
    {
        return $this->belongsTo('App\Models\Game\GameRole');
    }

    /**
     * Get the available players for replacement.
     */
    public function getPlayersForReplace()
    {
        return $this->match->availablePlayersQuery()
            ->where('user_id', '!=', null)
            ->where('team_id', null);
    }

    /**
     * Remove player and add guest on his position.
     *
     * @return bool
     */
    public function remove()
    {
        $this->user_id = null;
        $this->in_game_name = null;
        $this->status = Player::STATUS_GUEST;

        if ($this->save()) {
            return $this;
        }

        return false;
    }

    /**
     * Replace player.
     *
     * @return bool
     */
    public function replace()
    {
        $newPlayer = null;
        $ticketPlayer = $this->getPlayersForReplace()->where('status', static::STATUS_TICKET)->first();

        if ($ticketPlayer) {
            $newPlayer = $ticketPlayer;
        }

        if (!$newPlayer) {
            $subscriberPlayer = $this->getPlayersForReplace()->where('status', static::STATUS_SUBSCRIBER)->first();

            if ($subscriberPlayer) {
                $newPlayer = $subscriberPlayer;
            }
        }

        if (!$newPlayer) {
            $followerPlayer = $this->getPlayersForReplace()->where('status', static::STATUS_FOLLOWER)->first();

            if ($followerPlayer) {
                $newPlayer = $followerPlayer;
            }
        }

        if (!$newPlayer) {
            $viewerPlayer = $this->getPlayersForReplace()->where('status', static::STATUS_VIEWER)->first();

            if ($viewerPlayer) {
                $newPlayer = $viewerPlayer;
            }
        }

        if (!$newPlayer) {
            return false;
        }

        $newPlayer->team_id = $this->team_id;
        $newPlayer->game_role_id = $this->game_role_id;
        $newPlayer->position = $this->position;
        $newPlayer->save();

        $this->team_id = null;
        $this->game_role_id = null;
        $this->save();

        return $newPlayer;
    }

    /**
     * Get player by user and match.
     *
     * @param \App\Models\User\User $user
     * @param \App\Models\Match $match
     * @return \App\Models\Player\Player
     */
    public static function findByMatch(User $user, Match $match)
    {
        return static::where([
            ['user_id', $user->id],
            ['match_id', $match->id]
        ])->first();
    }

    /**
     * Create player by user and match.
     *
     * @param \App\Models\User\User $user
     * @param \App\Models\Match $match
     * @param \App\Models\Player\PlayerCard $card
     * @return \App\Models\Player\Player
     */
    public static function createForMatch(User $user, Match $match, PlayerCard $card)
    {
        return static::create([
            'user_id' => $user->id,
            'match_id' => $match->id,
            'in_game_name' => $card->in_game_name,
            'status' => static::determinePlayerStatus($user, $match)
        ]);
    }

    /**
     * Get player status for channel.
     *
     * @param \App\Models\User\User $user
     * @param \App\Models\Match $match
     * @return string
     */
    public static function determinePlayerStatus(User $user, Match $match)
    {
        $subscribed = UserChannelSubscription::where([
            'user_id' => $user->id,
            'channel_user_id' => $match->queue->user->id
        ])->first();

        if ($subscribed) {
            return static::STATUS_SUBSCRIBER;
        }

        $followings = TwitchApi::followings($user->provider_user_id, []);

        if (is_array($followings) && isset($followings['_total']) && $followings['_total'] && is_array($followings['follows'])) {
            foreach ($followings['follows'] as $following) {
                if ($following['channel']['_id'] == $match->queue->user->provider_user_id) {
                    return static::STATUS_FOLLOWER;
                }
            }
        }

        return static::STATUS_VIEWER;
    }

    /**
     * Mark player as ready.
     *
     * @param bool $status
     * @return bool
     */
    public function ready($status = false)
    {
        $this->ready = $status;

        return $this->save();
    }

    /**
     * Mark player as mvp.
     *
     * @return bool
     */
    public function mvp()
    {
        if ($this->match->completed_at || !$this->user_id) {
            return false;
        }

        $this->match->mvp = $this->user_id;

        return $this->match->save();
    }
}
