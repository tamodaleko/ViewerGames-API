<?php

namespace App\OAuth\Exceptions;

use League\OAuth2\Server\Exception\OAuthServerException;

class SocialGrantException extends OAuthServerException
{
    /**
     * @throws OAuthServerException
     */
    public static function invalidProvider()
    {
        return self::invalidRequest('provider', 'Invalid provider.');
    }

    /**
     * @param string $message
     *
     * @throws OAuthServerException
     */
    public static function customError($message)
    {
        return self::accessDenied($message);
    }
}
