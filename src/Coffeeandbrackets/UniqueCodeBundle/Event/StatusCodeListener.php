<?php

namespace Coffeeandbrackets\UniqueCodeBundle\Event;

use Coffeeandbrackets\UniqueCodeBundle\Entity\LogCode;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

class StatusCodeListener implements EventSubscriberInterface {

    private $em;

    public function __construct(EntityManager $em) {
        $this->em = $em;
    }

    public function onLeave(Event $event) {
        $logCode = new LogCode();
        $logCode->setDate(new \DateTime());
        $logCode->setFromStatus(implode(', ', array_keys($event->getMarking()->getPlaces())));
        $logCode->setToStatus(implode(', ', $event->getTransition()->getTos()));
        $logCode->setIsAdminAction(false);
        $logCode->setCode($event->getSubject());

        $this->em->persist($logCode);
        $this->em->flush();
    }

    public static function getSubscribedEvents() {
        return [
            'workflow.status_code.leave' => 'onLeave'
        ];
    }
}