<?php

namespace App\Services\Social;

use RuntimeException;

/** Message is Meta's error text (never contains a token) and is safe to show in admin. */
class SocialPublishException extends RuntimeException
{
    public bool $permanent = false;
}
