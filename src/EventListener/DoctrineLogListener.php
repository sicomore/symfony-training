<?php

namespace App\EventListener;

use App\Event\DoctrineLogEvent;
use Psr\Log\LoggerInterface;

class DoctrineLogListener
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onEntityCreate(DoctrineLogEvent $logEvent)
    {
        $this->logger->notice($logEvent->getMessage());
    }
}
