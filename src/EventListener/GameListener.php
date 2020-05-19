<?php

namespace App\EventListener;

use App\Entity\Pronostic;
use App\Event\DoctrineLogEvent;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;

class GameListener
{
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

//        if ($entity instanceof Pronostic) {
//        /** @var Pronostic $entity */
//        $date = $entity->getCreatedDate();
//
//
//        }
//
//
//
//        dd($entity);
    }
}
