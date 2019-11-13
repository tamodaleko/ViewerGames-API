<?php

namespace App\OAuth;

use App\Models\Badge;
use App\Models\Notification\Notification;
use App\Models\Provider;
use App\Models\Role;
use App\Models\User\User;
use App\Models\User\UserBadge;
use App\Models\User\UserChannelSubscription;
use App\OAuth\Exceptions\SocialGrantException;
use TwitchApi;

class SocialUserResolver
{
    /**
     * The minimum channel followers
     */
    const MIN_FOLLOWERS = 50;

    /**
     * Resolve user by given provider and access token.
     *
     * @param string $provider
     * @param string $authorizationCode
     * @param bool $streamer
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     *
     * @throws SocialGrantException
     */
    public function resolve($provider, $authorizationCode, $streamer = false)
    {
        $providerModel = Provider::where('name', $provider)->first();

        if (!$providerModel) {
            throw SocialGrantException::invalidProvider();
        }

        switch ($provider) {
            case 'twitch':
                return $this->authWithTwitch($providerModel, $authorizationCode, $streamer);
                break;
            default:
                throw SocialGrantException::invalidProvider();
                break;
        }
    }

    /**
     * Resolve user by Twitch access token.
     *
     * @param Provider $provider
     * @param string $authorizationCode
     * @param bool $streamer
     *
     * @return \App\Models\User\User
     */
    protected function authWithTwitch(Provider $provider, $authorizationCode, $streamer = false)
    {
        $twitchUser = $this->initUser($authorizationCode);

        if ($streamer && !in_array($twitchUser['name'], ['quicksales5000', 'viewergameslive'])) {
            $this->checkChannelFollowersCount($twitchUser['_id']);
        }

        $roleType = $streamer ? 'streamer' : 'player';

        $user = User::where('provider_id', $provider->id)->where('provider_user_id', $twitchUser['_id'])->first() ?: new User;
        $role = Role::where('name', $roleType)->first();

        $user->provider_id = $provider->id;
        $user->provider_user_id = $twitchUser['_id'];
        $user->username = $twitchUser['name'];
        $user->name = $twitchUser['display_name'];
        $user->email = $twitchUser['email'];
        $user->avatar = $twitchUser['logo'];

        if (!$user->save()) {
            throw SocialGrantException::customError('User could not be saved.');
        }

        if (!$user->notifications()->count()) {
            Notification::insertDefault($user->id);
        }

        if ($streamer && !$user->badges()->count()) {
            UserBadge::grantBadge(Badge::ID_STREAMER, $user->id);
        }

        if (!$streamer) {
            TwitchApi::setToken($authorizationCode);

            foreach (User::getChannels() as $channel) {
                $subscribed = TwitchApi::subscribedToChannel($channel->provider_user_id, $twitchUser['_id']);

                if (is_array($subscribed)) {
                    foreach ($user->channelSubscriptions as $single) {
                        $single->delete();
                    }
                    
                    UserChannelSubscription::create([
                        'user_id' => $user->id,
                        'channel_user_id' => $channel->id
                    ]);
                }
            }
        }

        $user->detachRoles($user->roles);
        $user->attachRole($role);

        return $user;
    }

    /**
     * Initialize user.
     *
     * @param string $authorizationCode
     *
     * @return array
     */
    private function initUser($authorizationCode)
    {
        // $response = TwitchApi::getAccessObject($authorizationCode);

        // if (isset($response->error) && $response->error) {
        //     throw SocialGrantException::customError($response->message);
        // }

        // $userObject = TwitchApi::authUser($response['access_token']);
        $userObject = TwitchApi::authUser($authorizationCode);

        if (isset($userObject->error) && $userObject->error) {
            throw SocialGrantException::customError($userObject->message);
        }

        return $userObject;
    }

    /**
     * Check for channel followers count.
     *
     * @param string $twitchUserId
     */
    private function checkChannelFollowersCount($twitchUserId)
    {
        $channel = TwitchApi::channel($twitchUserId);

        if ($channel['followers'] < self::MIN_FOLLOWERS) {
            throw SocialGrantException::customError('Streamer has less than ' . self::MIN_FOLLOWERS . ' followers.');
        }
    }
}
