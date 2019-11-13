<?php

namespace App\Http\Controllers;

use App\Http\Resources\MatchResource;
use App\Http\Resources\Player\PlayerResource;
use App\Http\Resources\Player\PlayerCardResource;
use App\Http\Resources\QueueResource;
use App\Http\Resources\QueueResourceCollection;
use App\Http\Resources\UserResource;
use App\Models\Notification\Notification;
use App\Models\Player\Player;
use App\Models\Queue\Queue;
use App\Models\Queue\QueueStatus;
use App\Models\User\User;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => [
                'allForUnAuthenticated',
                'showForUnAuthenticated',
                'activeForUnAuthenticated',
                'infoForUnAuthenticated'
            ]
        ]);
    }

    /**
     * Show user's queues.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\QueueResourceCollection
     */
    public function all(Request $request)
    {
        $queues = $request->user()->queues()->where('active', 0)->get();

        if (!count($queues)) {
            return $this->errorResponse('User has no queues created.');
        }

        return $this->wrapResponse(new QueueResourceCollection($queues));
    }

    /**
     * Show user's queue by id.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Queue\Queue $queue
     * @return \App\Http\Resources\QueueResource
     */
    public function show(Request $request, Queue $queue)
    {
        if ($request->user()->id !== $queue->user_id) {
            return $this->errorResponse('Access denied.', 401);
        }

        return $this->wrapResponse(new QueueResource($queue));
    }

    /**
     * Get information of selected queue.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Queue\Queue $queue
     * @return \Illuminate\Http\Response
     */
    public function info(Request $request, Queue $queue)
    {
        $match = $queue->getCurrentMatch();

        if (!$match && $queue->queue_status_id === QueueStatus::IN_PROGRESS) {
            $match = $queue->getLatestMatch();
        }

        $count = $match ? $match->getPlayerCount() : 0;

        return $this->wrapResponse([
            'queue' => new QueueResource($queue),
            'match' => $match ? new MatchResource($match) : null,
            'player_count' => $count
        ]);
    }

    /**
     * Show user's active queue.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\QueueResource
     */
    public function active(Request $request)
    {
        $queue = $request->user()->getActiveQueue();

        if (!$queue) {
            return $this->errorResponse('User has no active queue.');
        }

        return $this->wrapResponse(new QueueResource($queue));
    }

    /**
     * Show user's player card matching active queue and active queue.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function activeEnter(Request $request)
    {
        $queue = $request->user()->getActiveQueue();

        if (!$queue) {
            return $this->errorResponse('User has no active queue.');
        }

        $card = $request->user()->getActiveCard($queue);

        if (!$card) {
            return $this->errorResponse('User has no player card matching active queue.');
        }

        return $this->wrapResponse([
            'queue' => new QueueResource($queue),
            'card' => new PlayerCardResource($card)
        ]);
    }

    /**
     * Open user's active queue.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\MatchResource
     */
    public function activeOpen(Request $request)
    {
        $queue = $request->user()->getActiveQueue();

        if (!$queue) {
            return $this->errorResponse('User has no active queue.');
        }

        $status = $queue->queue_status_id;

        if (!$queue->open()) {
            return $this->errorResponse('Queue could not be opened.', 500);
        }

        $match = $queue->getCurrentMatch();
        $lastMatch = $queue->getLatestMatch();

        if ($status === QueueStatus::IN_PROGRESS && $lastMatch) {
            $oldPlayers = $lastMatch->players()
                ->where('status', '!=', Player::STATUS_STREAMER)
                ->where('status', '!=', Player::STATUS_GUEST)
                ->get();

            foreach ($oldPlayers as $oldPlayer) {
                Player::create([
                    'user_id' => $oldPlayer->user_id,
                    'match_id' => $match->id,
                    'in_game_name' => $oldPlayer->in_game_name,
                    'status' => $oldPlayer->status
                ]);
            }
        }

        return $this->wrapResponse(new MatchResource($match));
    }

    /**
     * Matchmaking process for active queue.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function activeMatchmaking(Request $request)
    {
        $queue = $request->user()->getActiveQueue();

        if (!$queue) {
            return $this->errorResponse('Matchmaking failed: User has no active queue.', 500);
        }

        $requirement = $queue->canBeFilled(); 

        if (!$requirement['success']) {
            return $this->errorResponse('Matchmaking failed: '. $requirement['response'], 500);
        }

        $match = $queue->getCurrentMatch();

        $guests = (isset($request->guests) && $request->guests) ? true : false;
        $matchmaking = $match->fillTeams($guests);

        if (!$matchmaking['success']) {
            return $this->errorResponse('Matchmaking failed: '. $matchmaking['response'], 500);
        }

        return $this->wrapResponse($match->getTeamsAndPlayers());
    }

    /**
     * Pause user's active queue.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function activePause(Request $request)
    {
        $queue = $request->user()->getActiveQueue();

        if (!$queue) {
            return $this->errorResponse('User has no active queue.');
        }

        if (!$queue->pause()) {
            return $this->errorResponse('Queue could not be paused.', 500);
        }

        return $this->successResponse('Queue has been paused successfully.');
    }

    /**
     * Resume user's active queue.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function activeResume(Request $request)
    {
        $queue = $request->user()->getActiveQueue();

        if (!$queue) {
            return $this->errorResponse('User has no active queue.');
        }

        if (!$queue->resume()) {
            return $this->errorResponse('Queue could not be resumed.', 500);
        }

        return $this->successResponse('Queue has been resumed successfully.');
    }

    /**
     * Confirm user's active queue.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function activeConfirm(Request $request)
    {
        $queue = $request->user()->getActiveQueue();

        if (!$queue) {
            return $this->errorResponse('User has no active queue.');
        }

        if (!$queue->confirm()) {
            return $this->errorResponse('Queue could not be confirmed.', 500);
        }

        return $this->wrapResponse($queue->getCurrentMatch()->getTeamsAndPlayers());
    }

    /**
     * Close user's active queue.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function activeClose(Request $request)
    {
        $queue = $request->user()->getActiveQueue();

        if (!$queue) {
            return $this->errorResponse('User has no active queue.');
        }

        if (!$queue->close()) {
            return $this->errorResponse('Queue could not be closed.', 500);
        }

        return $this->successResponse('Queue has been closed successfully.');
    }

    /**
     * End user's active queue.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function activeEnd(Request $request)
    {
        $queue = $request->user()->getActiveQueue();

        if (!$queue) {
            return $this->errorResponse('User has no active queue.');
        }

        $match = $queue->getCurrentMatch();

        if (!$match) {
            return $this->errorResponse('Queue does not have an ongoing match.', 500);
        }

        if (!$match->end()) {
            return $this->errorResponse('Match could not be ended.', 500);
        }

        return $this->wrapResponse($match->getTeamsAndPlayers());
    }

    /**
     * Activate user's queue.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Queue\Queue $queue
     * @return \Illuminate\Http\Response
     */
    public function activate(Request $request, Queue $queue)
    {
        if ($request->user()->id !== $queue->user_id) {
            return $this->errorResponse('Access denied.', 401);
        }

        foreach ($request->user()->queues as $userQueue) {
            $userQueue->queue_status_id = null;
            $userQueue->active = 0;
            $userQueue->save();
        }

        $queue->queue_status_id = QueueStatus::CLOSED;
        $queue->countdown_progress = $queue->countdown_timer;
        $queue->active = 1;

        if (!$queue->save()) {
            return $this->errorResponse('Queue could not be activated.', 500);
        }

        Notification::toggle($request->user()->id, 'no_active_queue', true);

        return $this->successResponse('Queue has been activated successfully.');
    }

    /**
     * Deactivate user's queue.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Queue\Queue $queue
     * @return \Illuminate\Http\Response
     */
    public function deactivate(Request $request, Queue $queue)
    {
        if ($request->user()->id !== $queue->user_id) {
            return $this->errorResponse('Access denied.', 401);
        }

        $queue->queue_status_id = null;
        $queue->countdown_progress = $queue->countdown_timer;
        $queue->active = 0;

        if (!$queue->save()) {
            return $this->errorResponse('Queue could not be deactivated.', 500);
        }

        Notification::toggle($request->user()->id, 'no_active_queue', false);

        return $this->successResponse('Queue has been deactivated successfully.');
    }

    /**
     * Mark player as ready for selected queue.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Queue\Queue $queue
     * @return \Illuminate\Http\Response
     */
    public function ready(Request $request, Queue $queue)
    {
        $match = $queue->getCurrentMatch();

        if (!$match) {
            return $this->errorResponse('Queue does not have an ongoing match.', 500);
        }

        $player = Player::findByMatch($request->user(), $match);
        $status = (isset($request->status) && $request->status) ? true : false;

        if (!$player || !$player->ready($status)) {
            return $this->errorResponse('Player\'s ready status could not be changed.', 500);
        }

        return $this->successResponse('Player\'s ready status has been changed successfully.');
    }

    /**
     * Join player for selected queue.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Queue\Queue $queue
     * @return \Illuminate\Http\Response
     */
    public function join(Request $request, Queue $queue)
    {
        $match = $queue->getCurrentMatch();

        if (!$match && $queue->queue_status_id === QueueStatus::IN_PROGRESS) {
            $match = $queue->getLatestMatch();
        }

        if (!$match) {
            return $this->errorResponse('Queue does not have an ongoing match.');
        }

        $card = $request->user()->getActiveCard($match->queue);

        if (!$card) {
            return $this->errorResponse('User has no player card matching active queue.');
        }

        $playerCheck = Player::findByMatch($request->user(), $match);
        $player = $playerCheck ?: Player::createForMatch($request->user(), $match, $card);

        if (!$player) {
            return $this->errorResponse('Player could not join queue.', 500);
        }

        return $this->wrapResponse([
            'queue' => new QueueResource($match->queue),
            'card' => new PlayerCardResource($card),
            'player' => new PlayerResource(Player::findByMatch($request->user(), $match)),
            'streamer' => new UserResource($queue->user)
        ]);
    }

    /**
     * Remove player for selected queue.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Queue\Queue $queue
     * @return \Illuminate\Http\Response
     */
    public function remove(Request $request, Queue $queue)
    {
        $match = $queue->getCurrentMatch();

        if (!$match && $queue->queue_status_id === QueueStatus::IN_PROGRESS) {
            $match = $queue->getLatestMatch();
        }

        if (!$match) {
            return $this->errorResponse('Queue does not have an ongoing match.', 500);
        }

        $player = Player::findByMatch($request->user(), $match);

        if (!$player) {
            return $this->errorResponse('Player has not joined the queue.', 500);
        }

        if (!$player->team_id && !$player->delete()) {
            return $this->errorResponse('Player could not be removed from queue.', 500);
        }

        return $this->successResponse('Player has been removed from queue successfully.');
    }

    /**
     * Change countdown timer for selected queue.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Queue\Queue $queue
     * @return \Illuminate\Http\Response
     */
    public function timer(Request $request, Queue $queue)
    {
        $queue->countdown_progress = $request->countdown_timer;

        if (!$queue->save()) {
            return $this->errorResponse('Countdown timer could not be updated.', 500);
        }

        return $this->wrapResponse([
            'countdown_timer' => $queue->countdown_timer,
            'countdown_progress' => $queue->countdown_progress
        ]);
    }

    /**
     * Create new user's queue.
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Http\Resources\QueueResource
     */
    public function store(Request $request)
    {
        $validated = $request->validate(Queue::$rules);
        $validated['user_id'] = $request->user()->id;
        $validated['countdown_progress'] = $request->countdown_timer;

        $queue = Queue::create($validated);

        if (!$queue) {
            return $this->errorResponse('Queue could not be created.', 500);
        }

        if ($queue->active) {
            foreach ($request->user()->queues as $userQueue) {
                if ($userQueue->id !== $queue->id) {
                    $userQueue->queue_status_id = null;
                    $userQueue->active = 0;
                    $userQueue->save();
                }
            }

            $queue->queue_status_id = QueueStatus::CLOSED;
            $queue->save();

            Notification::toggle($request->user()->id, 'no_active_queue', true);
        }

        return $this->wrapResponse(new QueueResource(Queue::find($queue->id)));
    }

    /**
     * Update user's queue.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Queue\Queue $queue
     * @return \App\Http\Resources\QueueResource
     */
    public function update(Request $request, Queue $queue)
    {
        $validated = $request->validate(Queue::$rules);
        $validated['user_id'] = $request->user()->id;
        $validated['countdown_progress'] = $request->countdown_timer;

        $queue->fill($validated);

        if (!$queue->save()) {
            return $this->errorResponse('Queue could not be updated.', 500);
        }

        if ($queue->active) {
            foreach ($request->user()->queues as $userQueue) {
                if ($userQueue->id !== $queue->id) {
                    $userQueue->queue_status_id = null;
                    $userQueue->active = 0;
                    $userQueue->save();
                }
            }

            $queue->queue_status_id = QueueStatus::CLOSED;
            $queue->save();

            Notification::toggle($request->user()->id, 'no_active_queue', true);
        }

        return $this->wrapResponse(new QueueResource(Queue::find($queue->id)));
    }

    /**
     * Delete user's queue.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Queue\Queue $queue
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Queue $queue)
    {
        if ($request->user()->id !== $queue->user_id) {
            return $this->errorResponse('Access denied.', 401);
        }

        if (!$queue->delete()) {
            return $this->errorResponse('Queue could not be deleted.', 500);
        }

        return $this->successResponse('Queue has been deleted successfully.');
    }

    /**
     * Get teams for user's queue.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Queue\Queue $queue
     * @return \Illuminate\Http\Response
     */
    public function teams(Request $request, Queue $queue)
    {
        $match = $queue->getCurrentMatch() ?: $queue->getLatestMatch();

        if (!$match) {
            return $this->errorResponse('Queue does not have an ongoing match.', 500);
        }

        $teams = $match->getTeamsAndPlayers();

        if (!$teams) {
            return $this->errorResponse('Teams not created yet.', 500);
        }

        return $this->wrapResponse($teams);
    }

    /**
     * Get player counts for user's queue.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Queue\Queue $queue
     * @return \Illuminate\Http\Response
     */
    public function counts(Request $request, Queue $queue)
    {
        $match = $queue->getCurrentMatch();

        if (!$match && $queue->queue_status_id === QueueStatus::IN_PROGRESS) {
            $match = $queue->getLatestMatch();
        }

        if (!$match) {
            return $this->wrapResponse([
                'match' => null,
                'counts' => [
                    'total' => 0,
                    'tickets' => 0,
                    'subscribers' => 0,
                    'followers' => 0,
                    'viewers' => 0
                ]
            ]);
        }

        return $this->wrapResponse($match->getPlayerCounts());
    }

    /**
     * Show queues for specified unauthenticated user.
     *
     * @param \App\Models\User\User $user
     * @return \App\Http\Resources\QueueResourceCollection
     */
    public function allForUnAuthenticated(User $user)
    {
        if (!$user->queues->count()) {
            return $this->errorResponse('User has no queues created.');
        }

        $queues = $user->queues()->where('active', 0)->get();

        return $this->wrapResponse(new QueueResourceCollection($queues));
    }

    /**
     * Show queue information for specified unauthenticated user.
     *
     * @param \App\Models\User\User $user
     * @param \App\Models\Queue\Queue $queue
     * @return \App\Http\Resources\QueueResourceCollection
     */
    public function infoForUnAuthenticated(User $user, Queue $queue)
    {
        $match = $queue->getCurrentMatch();

        if (!$match && $queue->queue_status_id === QueueStatus::IN_PROGRESS) {
            $match = $queue->getLatestMatch();
        }

        $count = $match ? $match->getPlayerCount() : 0;

        return $this->wrapResponse([
            'queue' => new QueueResource($queue),
            'match' => $match ? new MatchResource($match) : null,
            'player_count' => $count
        ]);
    }

    /**
     * Show specified queue for unauthenticated user.
     *
     * @param \App\Models\Queue\Queue $queue
     * @return \App\Http\Resources\QueueResource
     */
    public function showForUnAuthenticated(Queue $queue)
    {
        return $this->wrapResponse(new QueueResource($queue));
    }

    /**
     * Show active queue for specified unauthenticated user.
     *
     * @param \App\Models\User\User $user
     * @return \App\Http\Resources\QueueResource
     */
    public function activeForUnAuthenticated(User $user)
    {
        $queue = $user->getActiveQueue();

        if (!$queue) {
            return $this->errorResponse('User has no active queue.');
        }

        return $this->wrapResponse(new QueueResource($queue));
    }
}
