<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeBundle\Event;


use Coffeeandbrackets\UniqueCodeBundle\Entity\Code;
use Coffeeandbrackets\UniqueCodeBundle\Entity\CodeStatusChangeLog;
use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\CodeActivated;
use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\CustomerDeclined;
use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\HotelAccepted;
use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\ReservationCreated;
use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\HotelDeclined;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Workflow\Workflow;

/**
 * Responsible for listening to every event that would change the code status.
 * Logs any status change too.
 *
 * Class CodeStatusSubscriber
 * @package Coffeeandbrackets\UniqueCodeBundle\Event
 */
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
            CodeActivated::NAME => 'onReservationEvent',
            ReservationCreated::NAME => 'onReservationEvent',
            HotelAccepted::NAME => 'onReservationEvent',
            HotelDeclined::NAME => 'onReservationEvent',
            CustomerDeclined::NAME => 'onReservationEvent',
            'workflow.status_code.leave' => 'onCodeStatusChange'
        );
    }

    public function onReservationEvent(ReservationEvent $event)
    {
        $reservation = $event->getReservation();
        /**
         * @var $code Code
         */
        $code = $this->em->getRepository('UniqueCodeBundle:Code')->findOneBy(['code' => $reservation->getCode()]);
        if($reservation->getId())//make sure it is a real reservation (with id, etc) and not an early placeholder
            $code->setReservation($reservation);
        $code->setCampaign($reservation->getCampaign());
        $this->em->persist($code);

        switch ($event::NAME) {
            case CodeActivated::NAME :
                //an activated code could be resubmitted, so prevention
                if($code->getCurrentStatus() === 'not_actived')
                    $this->codeStatusWorkflow->apply($code, 'actif');
                break;
            case ReservationCreated::NAME :
                $this->codeStatusWorkflow->apply($code, 'request');
                break;
            case HotelAccepted::NAME :
                $this->codeStatusWorkflow->apply($code, 'accept');
                break;
            case HotelDeclined::NAME :
                $this->codeStatusWorkflow->apply($code, 'refuse');
                break;
            case CustomerDeclined::NAME :
                $this->codeStatusWorkflow->apply($code, 'refuse');
                break;
        }

        $this->em->flush();
    }

    public function onCodeStatusChange(Event $event) {
        /**
         * @var $code Code
         */
        $code = $event->getSubject();

        $logCode = new CodeStatusChangeLog();
        $logCode->setDate(new \DateTime());
        $logCode->setFromStatus(implode(', ', array_keys($event->getMarking()->getPlaces())));
        $logCode->setToStatus(implode(', ', $event->getTransition()->getTos()));
        $logCode->setIsAdminAction(false);
        $logCode->setCode($code);
        $reservation = $code->getReservation();
        if($reservation && $reservation->getId())//make sure it is a real reservation (with id, etc) and not an early placeholder
            $logCode->setReservation($reservation);
        $logCode->setCampaign($code->getCampaign());

        $this->em->persist($logCode);
        $this->em->flush();
    }
}