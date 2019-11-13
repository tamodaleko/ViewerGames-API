<?php

// Social Auth
Route::post('social_auth', 'Auth\SocialAuthController@handle');

/*
|--------------------------------------------------------------------------
| User Endpoints (Authenticated)
|--------------------------------------------------------------------------
*/

// General
Route::get('user', 'UserController@show');
Route::get('user/socials', 'SocialController@show');
Route::get('user/schedules', 'ScheduleController@all');
Route::get('user/mvps', 'MatchController@mvps');

Route::post('user/socials', 'SocialController@store');
Route::post('user/schedules', 'ScheduleController@store');
Route::post('user/schedules/form', 'ScheduleController@form');
Route::post('user/settings', 'UserSettingController@store');

// Queue
Route::get('user/queues', 'QueueController@all');
Route::get('user/queues/active', 'QueueController@active');
Route::get('user/queues/{queue}', 'QueueController@show');
Route::get('user/queues/{queue}/info', 'QueueController@info');
Route::get('user/queues/{queue}/match/active/teams', 'QueueController@teams');
Route::get('user/queues/{queue}/match/active/counts', 'QueueController@counts');
Route::post('user/queues/{queue}/activate', 'QueueController@activate');
Route::post('user/queues/{queue}/deactivate', 'QueueController@deactivate');
Route::post('user/queues/{queue}/ready', 'QueueController@ready');
Route::post('user/queues/{queue}/join', 'QueueController@join');
Route::post('user/queues/{queue}/remove', 'QueueController@remove');
Route::post('user/queues/{queue}/timer', 'QueueController@timer');
Route::post('user/queues', 'QueueController@store');
Route::put('user/queues/{queue}', 'QueueController@update');
Route::delete('user/queues/{queue}', 'QueueController@destroy');

// Active Queue Process
Route::post('user/queues/active/enter', 'QueueController@activeEnter');
Route::post('user/queues/active/open', 'QueueController@activeOpen');
Route::post('user/queues/active/matchmaking', 'QueueController@activeMatchmaking');
Route::post('user/queues/active/pause', 'QueueController@activePause');
Route::post('user/queues/active/resume', 'QueueController@activeResume');
Route::post('user/queues/active/confirm', 'QueueController@activeConfirm');
Route::post('user/queues/active/close', 'QueueController@activeClose');
Route::post('user/queues/active/end', 'QueueController@activeEnd');

// Notification
Route::get('user/notifications', 'NotificationController@all');
Route::post('user/notifications/{notification}/remove', 'NotificationController@remove');

// PlayerCard
Route::get('user/cards', 'PlayerCardController@all');
Route::get('user/cards/{card}', 'PlayerCardController@show');
Route::post('user/cards', 'PlayerCardController@store');
Route::put('user/cards/{card}', 'PlayerCardController@update');
Route::delete('user/cards/{card}', 'PlayerCardController@destroy');

// Player
Route::post('user/players/{player}/remove', 'PlayerController@remove');
Route::post('user/players/{player}/replace', 'PlayerController@replace');
Route::post('user/players/{player}/mvp', 'PlayerController@mvp');

// Team
Route::post('user/teams/{team}/winner', 'TeamController@winner');

// Match
Route::get('user/matches/completed', 'MatchController@completed');
Route::get('user/matches/{match}/teams', 'MatchController@teams');

// Badge
Route::get('user/badges', 'BadgeController@all');
/*
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| User Endpoints (UnAuthenticated)
|--------------------------------------------------------------------------
*/

// General
Route::get('users/{id}', 'UserController@showForUnAuthenticated')->where('id', '[0-9]+');
Route::get('users/{username}', 'UserController@showByUsernameForUnAuthenticated')->where('username', '[a-z0-9_]+');

Route::get('users/{user}/socials', 'SocialController@showForUnAuthenticated');
Route::get('users/{user}/schedules', 'ScheduleController@allForUnAuthenticated');
Route::get('users/{user}/mvps', 'MatchController@mvpsForUnAuthenticated');

//Queue
Route::get('users/{user}/queues', 'QueueController@allForUnAuthenticated');
Route::get('users/{user}/queues/{queue}/info', 'QueueController@infoForUnAuthenticated');
Route::get('users/{user}/queues/active', 'QueueController@activeForUnAuthenticated');

//PlayerCard
Route::get('users/{user}/cards', 'PlayerCardController@allForUnAuthenticated');

// Match
Route::get('users/{user}/matches/completed', 'MatchController@completedForUnAuthenticated');
Route::get('users/{user}/matches/{match}/teams', 'MatchController@teamsForUnAuthenticated');

// Badge
Route::get('users/{user}/badges', 'BadgeController@allForUnAuthenticated');
/*
|--------------------------------------------------------------------------
*/

// Game Endpoints
Route::get('games', 'GameController@allForUnAuthenticated');
Route::get('games/{game}/info', 'GameController@infoForUnAuthenticated');

// Queue Endpoints
Route::get('queues/{queue}', 'QueueController@showForUnAuthenticated');

// PlayerCard Endpoints
Route::get('cards/{card}', 'PlayerCardController@showForUnAuthenticated');
