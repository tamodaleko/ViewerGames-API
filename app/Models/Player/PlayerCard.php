<?php

namespace App\Models\Player;

use App\Models\Queue\Queue;
use Illuminate\Database\Eloquent\Model;

class PlayerCard extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'game_id', 'game_server_id', 'game_rank_id', 'game_role_id', 'in_game_name'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'game_id' => 'required|integer',
        'game_server_id' => 'required|integer',
        'game_rank_id' => 'nullable|integer',
        'game_role_id' => 'nullable|integer',
        'in_game_name' => 'required|string|max:255'
    ];

    /**
     * Get the user record associated with the player card.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User\User');
    }

    /**
     * Get the game record associated with the player card.
     */
    public function game()
    {
        return $this->belongsTo('App\Models\Game\Game');
    }

    /**
     * Get the game server record associated with the player card.
     */
    public function gameServer()
    {
        return $this->belongsTo('App\Models\Game\GameServer');
    }

    /**
     * Get the game rank record associated with the player card.
     */
    public function gameRank()
    {
        return $this->belongsTo('App\Models\Game\GameRank');
    }

    /**
     * Get the game role record associated with the player card.
     */
    public function gameRole()
    {
        return $this->belongsTo('App\Models\Game\GameRole');
    }

    /**
     * Check combination of game and server.
     *
     * @param int $userId
     * @param int $gameId
     * @param int $gameServerId
     * @return bool
     */
    public static function checkGameServerCombination($userId, $gameId, $gameServerId)
    {
        $card = static::where('user_id', $userId)
            ->where('game_id', $gameId)
            ->where('game_server_id', $gameServerId)
            ->first();

        return $card ? true : false;
    }

    /**
     * Get user's player card for queue.
     *
     * @param int $userId
     * @param \App\Models\Queue\Queue $queue
     * @return \App\Models\Player\PlayerCard
     */
    public static function getCardForUser($userId, Queue $queue)
    {
        $card = static::where([
            ['user_id', $userId],
            ['game_id', $queue->game_id],
            ['game_server_id', $queue->game_server_id]
        ]);

        if ($queue->game_rank_id) {
            $card->where('game_rank_id', $queue->game_rank_id);
        }

        return $card->first();
    }

    /**
     * Get player cards for selected user ids and role.
     *
     * @param \App\Models\Queue\Queue $queue
     * @param array $userIds
     * @param int $roleId
     * @param int $limit
     * @return \App\Models\Player\PlayerCard
     */
    public static function findCardsForRole(Queue $queue, $userIds = [], $roleId, $limit = 1)
    {
        $card = static::whereIn('user_id', $userIds)
            ->where('game_id', $queue->game_id)
            ->where('game_server_id', $queue->game_server_id)
            ->where('game_role_id', $roleId);

        if ($queue->game_rank_id) {
            $card->where('game_rank_id', $queue->game_rank_id);
        }

        if ($limit) {
            $card->limit($limit);
        }

        return $card->get();
    }
}
