<?php
declare(strict_types=1);

namespace Ankurk91\SesWebhooks\Exception;

use Exception;

class WebhookFailed extends Exception
{
    public static function jobClassDoesNotExist(string $jobClass): self
    {
        return new static("Could not process ses webhook, the configured class `$jobClass` not found.");
    }
}
