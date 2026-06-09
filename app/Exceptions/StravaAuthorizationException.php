<?php

namespace App\Exceptions;

use Exception;

class StravaAuthorizationException extends Exception
{
    public function __construct(
        string $message = 'Strava authorization failed. Please reconnect your account.',
        public readonly ?int $userId = null,
    ) {
        parent::__construct($message);
    }
}
