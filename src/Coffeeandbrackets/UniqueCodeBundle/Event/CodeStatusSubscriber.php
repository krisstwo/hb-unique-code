<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeBundle\Event;


use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\CustomerAccepted;
use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\CustomerDeclined;
use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\HotelAccepted;
use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\ReservationCreated;
use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\HotelDeclined;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Workflow;

class CodeStatusSubscriber implements EventSubscriberInterface
{
    /**
     * @var Workflow
     */
    private $codeStatusWorkflow;

    /**
     * @var EntityManager
     */
    private $em;//TODO: Remove persistence logic, reservation needs to aggregate code

    /**
     * CodeStatusSubscriber constructor.
     * @param Workflow $codeStatusWorkflow
     * @param EntityManager $em
     */
    public function __construct(Workflow $codeStatusWorkflow, EntityManager $em)
    {
        $this->codeStatusWorkflow = $codeStatusWorkflow;
        $this->em = $em;
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
            ReservationCreated::NAME => 'onReservationEvent',
            HotelAccepted::NAME => 'onReservationEvent',
            HotelDeclined::NAME => 'onReservationEvent',
            CustomerAccepted::NAME => 'onReservationEvent',
            CustomerDeclined::NAME => 'onReservationEvent'
        );
    }

    public function onReservationEvent(ReservationEvent $event)
    {
        $reservation = $event->getReservation();
        $code = $this->em->getRepository('UniqueCodeBundle:Code')->findOneBy(['code' => $reservation->getCode()]);

        switch ($event::NAME) {
            //TODO: when does code status pass to active ?
            case ReservationCreated::NAME :
                $this->codeStatusWorkflow->apply($code, 'request');
                break;
            case HotelAccepted::NAME :
                $this->codeStatusWorkflow->apply($code, 'accept');
                break;
            case HotelDeclined::NAME :
                $this->codeStatusWorkflow->apply($code, 'refuse');
                break;
            case CustomerAccepted::NAME :
                $this->codeStatusWorkflow->apply($code, 'accept');
                break;
            case CustomerDeclined::NAME :
                $this->codeStatusWorkflow->apply($code, 'refuse');
                break;
        }

        $this->em->flush();
    }
}