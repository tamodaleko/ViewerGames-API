<?php

namespace App\Models\Queue;

use App\Models\Match;
use App\Models\Player\Player;
use App\Models\Player\PlayerCard;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    const SM_SPECTATE = 0;
    const SM_PLAY_ALONG = 1;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'role_enforcement' => 'boolean',
        'active' => 'boolean'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'game_id', 'game_map_id', 'game_server_id', 'game_rank_id', 'role_enforcement', 'streamer_mode',
        'active', 'team_count', 'players_per_team', 'min_ticket_cost', 'countdown_timer', 'countdown_progress'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'game_id' => 'required|integer',
        'game_map_id' => 'nullable|integer',
        'game_server_id' => 'required|integer',
        'game_rank_id' => 'nullable|integer',
        'role_enforcement' => 'required|boolean',
        'streamer_mode' => 'required|boolean',
        'active' => 'required|boolean',
        'team_count' => 'required|integer',
        'players_per_team' => 'required|integer',
        'min_ticket_cost' => 'required|numeric',
        'countdown_timer' => 'required|integer'
    ];

    /**
     * Streamer modes
     *
     * @var array
     */
    public static $streamerModes = [
        self::SM_SPECTATE => 'Spectate',
        self::SM_PLAY_ALONG => 'Play-Along'
    ];

    /**
     * @inheritdoc
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }

    /**
     * Get the user record associated with the queue.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User\User');
    }

    /**
     * Get the queue status record associated with the queue.
     */
    public function status()
    {
        return $this->belongsTo('App\Models\Queue\QueueStatus', 'queue_status_id');
    }

    /**
     * Get the game record associated with the queue.
     */
    public function game()
    {
        return $this->belongsTo('App\Models\Game\Game');
    }

    /**
     * Get the game server record associated with the queue.
     */
    public function gameServer()
    {
        return $this->belongsTo('App\Models\Game\GameServer');
    }

    /**
     * Get the game map record associated with the queue.
     */
    public function gameMap()
    {
        return $this->belongsTo('App\Models\Game\GameMap');
    }

    /**
     * Get the game rank record associated with the queue.
     */
    public function gameRank()
    {
        return $this->belongsTo('App\Models\Game\GameRank');
    }

    /**
     * Open active queue.
     *
     * @return bool
     */
    public function open()
    {
        $currentMatch = $this->getCurrentMatch();

        if ($currentMatch) {
            $currentMatch->delete();
        }

        $match = Match::create(['queue_id' => $this->id]);

        if (!$match) {
            return false;
        }

        $this->queue_status_id = QueueStatus::OPEN;
        $this->countdown_progress = $this->countdown_timer;

        if (!$this->save()) {
            $match->delete();
            return false;
        }

        if ($this->streamer_mode === static::SM_PLAY_ALONG) {
            $card = PlayerCard::getCardForUser($this->user_id, $this);

            Player::create([
                'user_id' => $this->user_id,
                'match_id' => $match->id,
                'in_game_name' => $card ? $card->in_game_name : null,
                'status' => Player::STATUS_STREAMER,
                'ready' => 1,
                'position' => 1
            ]);
        }

        return true;
    }

    /**
     * Pause active queue.
     *
     * @return bool
     */
    public function pause()
    {
        if ($this->queue_status_id !== QueueStatus::OPEN) {
            return false;
        }

        $this->queue_status_id = QueueStatus::PAUSED;

        return $this->save();
    }

    /**
     * Resume active queue.
     *
     * @return bool
     */
    public function resume()
    {
        if ($this->queue_status_id !== QueueStatus::PAUSED) {
            return false;
        }

        $this->queue_status_id = QueueStatus::OPEN;

        return $this->save();
    }

    /**
     * Confirm active queue.
     *
     * @return bool
     */
    public function confirm()
    {
        if ($this->queue_status_id !== QueueStatus::OPEN) {
            return false;
        }

        $this->queue_status_id = QueueStatus::IN_PROGRESS;

        return $this->save();
    }

    /**
     * Close active queue.
     *
     * @return bool
     */
    public function close()
    {
        $currentMatch = $this->getCurrentMatch();

        if ($currentMatch) {
            $currentMatch->delete();
        }

        $this->queue_status_id = QueueStatus::CLOSED;
        $this->countdown_progress = $this->countdown_timer;

        return $this->save();
    }

    /**
     * Get current match for active queue.
     *
     * @return \App\Models\Match
     */
    public function getCurrentMatch()
    {
        return Match::where([
            ['queue_id', $this->id],
            ['completed_at', null]
        ])
        ->orderBy('created_at', 'desc')
        ->first();
    }

    /**
     * Get last completed match for active queue.
     *
     * @return \App\Models\Match
     */
    public function getLatestMatch()
    {
        return Match::where([
            ['queue_id', $this->id],
            ['completed_at', '!=', null]
        ])
        ->orderBy('completed_at', 'desc')
        ->first();
    }

    /**
     * Check matchmaking requirements.
     *
     * @return array
     */
    public function canBeFilled()
    {
        if ($this->queue_status_id !== QueueStatus::OPEN) {
            return [
                'success' => false,
                'response' => 'Queue has not been opened yet.' 
            ];
        }

        if (!$this->getCurrentMatch()) {
            return [
                'success' => false,
                'response' => 'Match has not been created yet or it has been completed already.' 
            ];
        }

        return ['success' => true];
    }
}
