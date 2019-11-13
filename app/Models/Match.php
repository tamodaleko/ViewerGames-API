<?php

namespace App\Models;

use App\Http\Resources\MatchResource;
use App\Models\Player\Player;
use App\Models\Player\PlayerCard;
use App\Models\Queue\Queue;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Match extends Model
{
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'completed_at'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['queue_id'];

    /**
     * Get the queue associated with the match.
     */
    public function queue()
    {
        return $this->belongsTo('App\Models\Queue\Queue');
    }

    /**
     * Get the mvp associated with the match.
     */
    public function mvpUser()
    {
        return $this->belongsTo('App\Models\User\User', 'mvp');
    }

    /**
     * Get the players for the match.
     */
    public function players()
    {
        return $this->hasMany('App\Models\Player\Player');
    }

    /**
     * Get the teams for the match.
     */
    public function teams()
    {
        return $this->hasMany('App\Models\Team');
    }

    /**
     * Get the available players for the match.
     */
    public function availablePlayersQuery()
    {
        return $this->players()->where('ready', 1);
    }

    /**
     * Create teams and fill positions for match.
     *
     * @param bool $guests
     * @return array
     */
    public function fillTeams($guests = false)
    {
        if ($this->teams()->count()) {
            return ['success' => true];
        }

        $count = $this->availablePlayersQuery()->count();
        
        if (!$count) {
            return [
                'success' => false,
                'response' => 'There are no available players.'
            ];
        }

        $availableSlots = $this->queue->players_per_team * $this->queue->team_count;

        if ($count < $availableSlots && !$guests) {
            return [
                'success' => false,
                'response' => 'There are not enough players.'
            ];
        }

        $teams = $this->getOrCreateTeams();
        
        $team1 = (isset($teams[0]) && $teams[0]) ? $teams[0]->id : null;
        $team2 = (isset($teams[1]) && $teams[1]) ? $teams[1]->id : null;

        $availableRoles = $this->getAvailableRoles(count($teams));

        $currentPosition = 1;

        if ($this->queue->streamer_mode === Queue::SM_PLAY_ALONG) {
            if ($this->addStreamer($team1, $availableRoles)) {
                $availableSlots--;
                $currentPosition = 2;
            }
        }

        if ($this->queue->role_enforcement) {
            $this->addAllPlayersByRoles($team1, $team2, $availableSlots, $availableRoles, $currentPosition);
        } else {
            $this->addAllPlayersBySlots($team1, $team2, $availableSlots, $currentPosition);
        }

        return ['success' => true];
    }

    /**
     * Add all players by roles for the match.
     *
     * @param int $team1
     * @param int $team2
     * @param int $availableSlots
     * @param array $availableRoles
     * @param int $currentPosition
     *
     * @return void
     */
    private function addAllPlayersByRoles($team1, $team2, &$availableSlots, &$availableRoles, &$currentPosition)
    {
        $this->addPlayersByRoles(Player::STATUS_TICKET, $team1, $team2, $availableSlots, $availableRoles, $currentPosition);

        if ($availableSlots) {
            $this->addPlayersByRoles(Player::STATUS_SUBSCRIBER, $team1, $team2, $availableSlots, $availableRoles, $currentPosition);
        }

        if ($availableSlots) {
            $this->addPlayersByRoles(Player::STATUS_FOLLOWER, $team1, $team2, $availableSlots, $availableRoles, $currentPosition);
        }

        if ($availableSlots) {
            $this->addPlayersByRoles(Player::STATUS_VIEWER, $team1, $team2, $availableSlots, $availableRoles, $currentPosition);
        }

        if ($availableSlots) {
            $this->addGuestPlayersByRoles($team1, $team2, $availableSlots, $availableRoles, $currentPosition);
        }
    }

    /**
     * Add players by roles for the match.
     *
     * @param string $status
     * @param int $team1
     * @param int $team2
     * @param int $availableSlots
     * @param array $availableRoles
     * @param int $currentPosition
     *
     * @return void
     */
    private function addPlayersByRoles($status, $team1, $team2, &$availableSlots, &$availableRoles, &$currentPosition)
    {
        $players = $this->availablePlayersQuery()->where('status', $status);
        $userIds = array_pluck($players->get(), 'user_id');

        $team = (($availableSlots % 2 !== 0) && $team2) ? $team2 : $team1;

        foreach ($availableRoles as $roleId => $role) {
            if (!$availableSlots || !$userIds) {
                continue;
            }

            $available = ($role['available'] <= count($userIds)) ? $role['available'] : 1;

            $cards = PlayerCard::findCardsForRole($this->queue, $userIds, $roleId, $available);

            if (!$cards) {
                continue;
            }

            $playersChosen = $this->availablePlayersQuery()
                ->where('status', $status)
                ->whereIn('user_id', array_pluck($cards, 'user_id'))
                ->get();

            foreach ($playersChosen as $player) {
                $player->team_id = $team;
                $player->game_role_id = $roleId;
                $player->position = $currentPosition;
                $player->save();

                // Remove player from available list
                if (($key = array_search($player->user_id, $userIds)) !== false) {
                    array_forget($userIds, $key);
                }

                // Remove slot
                $availableSlots--;

                // Remove role
                $availableRoles[$roleId]['available']--;

                // Increase position
                $currentPosition++;

                // Change team
                $team = (($availableSlots % 2 !== 0) && $team2) ? $team2 : $team1;
            }
        }

        foreach ($availableRoles as $roleId => $role) {
            if (!$availableSlots || !$userIds) {
                continue;
            }

            if (!$role['available']) {
                array_forget($availableRoles, $roleId);
                continue;
            }

            $playersChosen = $this->availablePlayersQuery()
                ->where('status', $status)
                ->whereIn('user_id', array_random($userIds, $role['available']))
                ->get();

            foreach ($playersChosen as $player) {
                $player->team_id = $team;
                $player->game_role_id = $roleId;
                $player->position = $currentPosition;
                $player->save();

                // Remove player from available list
                if (($key = array_search($player->user_id, $userIds)) !== false) {
                    array_forget($userIds, $key);
                }

                // Remove slot
                $availableSlots--;

                // Increase position
                $currentPosition++;

                // Change team
                $team = (($availableSlots % 2 !== 0) && $team2) ? $team2 : $team1;
            }

            array_forget($availableRoles, $roleId);
        }
    }

    /**
     * Add guest players by roles for the match.
     *
     * @param int $team1
     * @param int $team2
     * @param int $availableSlots
     * @param array $availableRoles
     * @param int $currentPosition
     *
     * @return void
     */
    private function addGuestPlayersByRoles($team1, $team2, &$availableSlots, &$availableRoles, &$currentPosition)
    {
        $team = (($availableSlots % 2 !== 0) && $team2) ? $team2 : $team1;

        foreach ($availableRoles as $roleId => $role) {
            if (!$availableSlots) {
                continue;
            }

            for ($i = 1; $i <= $role['available']; $i++) {
                Player::create([
                    'user_id' => null,
                    'match_id' => $this->id,
                    'team_id' => $team,
                    'game_role_id' => $roleId,
                    'ready' => 1,
                    'position' => $currentPosition,
                    'status' => Player::STATUS_GUEST
                ]);

                // Remove slot
                $availableSlots--;

                // Increase position
                $currentPosition++;

                // Change team
                $team = (($availableSlots % 2 !== 0) && $team2) ? $team2 : $team1;
            }

            // Remove role and slot
            array_forget($availableRoles, $roleId);
        }
    }

    /**
     * Add all players by slots for the match.
     *
     * @param int $team1
     * @param int $team2
     * @param int $availableSlots
     * @param int $currentPosition
     *
     * @return void
     */
    private function addAllPlayersBySlots($team1, $team2, &$availableSlots, &$currentPosition)
    {
        $this->addPlayersBySlots(Player::STATUS_TICKET, $team1, $team2, $availableSlots, $currentPosition);

        if ($availableSlots) {
            $this->addPlayersBySlots(Player::STATUS_SUBSCRIBER, $team1, $team2, $availableSlots, $currentPosition);
        }

        if ($availableSlots) {
            $this->addPlayersBySlots(Player::STATUS_FOLLOWER, $team1, $team2, $availableSlots, $currentPosition);
        }

        if ($availableSlots) {
            $this->addPlayersBySlots(Player::STATUS_VIEWER, $team1, $team2, $availableSlots, $currentPosition);
        }

        if ($availableSlots) {
            $this->addGuestPlayersBySlots($team1, $team2, $availableSlots, $currentPosition);
        }
    }

    /**
     * Add players by slots for the match.
     *
     * @param string $status
     * @param int $team1
     * @param int $team2
     * @param int $availableSlots
     * @param int $currentPosition
     *
     * @return void
     */
    private function addPlayersBySlots($status, $team1, $team2, &$availableSlots, &$currentPosition)
    {
        $players = $this->availablePlayersQuery()->where('status', $status)->get();
        $team = (($availableSlots % 2 !== 0) && $team2) ? $team2 : $team1;

        foreach ($players as $player) {
            if (!$availableSlots) {
                break;
            }

            $player->team_id = $team;
            $player->position = $currentPosition;
            $player->save();

            // Remove slot
            $availableSlots--;

            // Increase position
            $currentPosition++;

            // Change team
            $team = (($availableSlots % 2 !== 0) && $team2) ? $team2 : $team1;
        }
    }

    /**
     * Add guest players by slots for the match.
     *
     * @param int $team1
     * @param int $team2
     * @param int $availableSlots
     * @param int $currentPosition
     *
     * @return void
     */
    private function addGuestPlayersBySlots($team1, $team2, &$availableSlots, &$currentPosition)
    {
        if (($availableSlots % 2 !== 0) && $team2) {
            $teamChosen = 'team2';
            $team = $team2;
        } else {
            $teamChosen = 'team1';
            $team = $team1;
        }

        for ($i = 1; $i <= $availableSlots; $i++) {
            Player::create([
                'match_id' => $this->id,
                'team_id' => $team,
                'ready' => 1,
                'position' => $currentPosition,
                'status' => Player::STATUS_GUEST
            ]);

            // Increase position
            $currentPosition++;

            // Change team
            if ($teamChosen === 'team1' && $team2) {
                $teamChosen = 'team2';
                $team = $team2;
            } else {
                $teamChosen = 'team1';
                $team = $team1;
            }
        }
    }

    /**
     * Add streamer to the team for the match.
     *
     * @param int $teamId
     * @param array $roles
     * @return bool
     */
    private function addStreamer($teamId, &$roles)
    {
        $streamer = $this->availablePlayersQuery()
            ->where('user_id', $this->queue->user_id)
            ->where('status', Player::STATUS_STREAMER)
            ->first();

        if (!$streamer) {
            return false;
        }

        if ($this->queue->role_enforcement) {
            $card = PlayerCard::getCardForUser($streamer->user_id, $this->queue);

            if ($card && isset($roles[$card->game_role_id]) && $roles[$card->game_role_id]['available']) {
                $streamer->game_role_id = $card->game_role_id;
                $roles[$card->game_role_id]['available']--;
            }
        }

        $streamer->team_id = $teamId;

        return $streamer->save();
    }

    /**
     * Get/Create teams for match.
     *
     * @return array
     */
    public function getOrCreateTeams()
    {
        if (!$this->teams()->count()) {
            for ($i = 1; $i <= $this->queue->team_count; $i++) {
                Team::create([
                    'match_id' => $this->id,
                    'winner_status' => false
                ]);
            }
        }

        return $this->teams()->get();
    }

    /**
     * Get available roles for the match.
     *
     * @param int $teamCount
     * @return array
     */
    private function getAvailableRoles($teamCount = 1)
    {
        $roles = [];

        foreach ($this->queue->game->roles as $role) {
            if (in_array($role->name, ['DPS', 'Fill'])) {
                $available = $teamCount * 2;
            } else {
                $available = $teamCount;
            }
            
            $roles[$role->id] = [
                'available' => $available
            ];
        }

        return $roles;
    }

    /**
     * Get teams and players information for the match.
     *
     * @param bool $includeMatch
     * @return array
     */
    public function getTeamsAndPlayers($includeMatch = true)
    {
        if ($includeMatch) {
            $data['match'] = new MatchResource($this);
        }

        $teams = $this->teams;
        $team1 = (isset($teams[0]) && $teams[0]) ? $teams[0] : null;
        $team2 = (isset($teams[1]) && $teams[1]) ? $teams[1] : null;

        $roles = true;

        if (!$this->queue->role_enforcement) {
            $roles = false;
        }

        $positionTeam1 = 1;
        $positionTeam2 = 1;

        if ($team1) {
            $data['team1']['id'] = isset($team1->id) ? $team1->id : 0;
            $data['team1']['winner'] = isset($team1->winner_status) ? $team1->winner_status : 0;
        }

        if ($team2) {
            $data['team2']['id'] = isset($team2->id) ? $team2->id : 0;
            $data['team2']['winner'] = isset($team2->winner_status) ? $team2->winner_status : 0;
        }

        $players = $this->players()->orderBy('position')->get();

        foreach ($players as $player) {
            if (!$player->team_id) {
                continue;
            }

            $team = ($player->team_id === $team1->id) ? 'team1' : 'team2';

            if (!$roles) {
                if ($team === 'team1') {
                    $position = $positionTeam1;
                    $positionTeam1++;
                } else {
                    $position = $positionTeam2;
                    $positionTeam2++;
                }

                $role = 'Player' . $position;
            } else {
                $role = $player->gameRole ? $player->gameRole->name : null;
            }

            $data[$team]['players'][] = [
                'player_id' => $player->id,
                'user_id' => $player->user_id,
                'username' => $player->user ? $player->user->username : null,
                'name' => $player->user ? $player->user->name : null,
                'in_game_name' => $player->in_game_name,
                'avatar' => $player->user ? $player->user->avatar : null,
                'role' => $role,
                'status' => $player->status
            ];
        }

        return $data;
    }

    /**
     * Get player count for the match.
     *
     * @param string $type
     * @return int
     */
    public function getPlayerCount($type = 'total')
    {
        switch ($type) {
            case 'total':
                return $this->players()->where('status', '!=', Player::STATUS_STREAMER)->where('status', '!=', Player::STATUS_GUEST)->count();
                break;
            case 'tickets':
                return $this->players()->where('status', Player::STATUS_TICKET)->count();
                break;
            case 'subscribers':
                return $this->players()->where('status', Player::STATUS_SUBSCRIBER)->count();
                break;
            case 'followers':
                return $this->players()->where('status', Player::STATUS_FOLLOWER)->count();
                break;
            case 'viewers':
                return $this->players()->where('status', Player::STATUS_VIEWER)->count();
                break;
        }

        return 0;
    }

    /**
     * Get player counts for the match.
     *
     * @return array
     */
    public function getPlayerCounts()
    {
        $data['match'] = new MatchResource($this);

        $data['counts'] = [
            'total' => $this->getPlayerCount('total'),
            'tickets' => $this->getPlayerCount('tickets'),
            'subscribers' => $this->getPlayerCount('subscribers'),
            'followers' => $this->getPlayerCount('followers'),
            'viewers' => $this->getPlayerCount('viewers') 
        ];

        return $data;
    }

    /**
     * End the match.
     *
     * @return bool
     */
    public function end()
    {
        if ($this->completed_at) {
            return false;
        }

        $this->completed_at = Carbon::now()->toDateTimeString();

        return $this->save();
    }
}
