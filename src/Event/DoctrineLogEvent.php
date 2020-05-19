<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class DoctrineLogEvent extends Event
{
    public const NAME = 'doctrine.log.event';

    private $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }
}
