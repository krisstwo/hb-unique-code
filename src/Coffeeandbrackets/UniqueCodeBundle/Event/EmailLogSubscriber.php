<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeBundle\Event;


use Coffeeandbrackets\UniqueCodeBundle\Entity\EmailLog;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 *
 *
 * Class CodeStatusSubscriber
 * @package Coffeeandbrackets\UniqueCodeBundle\Event
 */
class EmailLogSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            EmailEvent::NAME => 'onEmail',
        );
    }

    public function onEmail(EmailEvent $event)
    {
        $emailLog = new EmailLog();
        $emailLog->setEvent($event::NAME);
        $emailLog->setEventDate(new \DateTime());
        $emailLog->setFrom($event->getFrom());
        $emailLog->setTo($event->getTo());
        $emailLog->setBcc($event->getBcc());
        $emailLog->setSubject($event->getSubject());
        $emailLog->setBody($event->getBody());
        $emailLog->setReservation($event->getReservation());

        $this->em->persist($emailLog);
        $this->em->flush();
    }
}