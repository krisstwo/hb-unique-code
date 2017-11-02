<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeBundle\Event;


use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\ReservationCreated;
use Coffeeandbrackets\UniqueCodeBundle\Service\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 *
 *
 * Class CodeStatusSubscriber
 * @package Coffeeandbrackets\UniqueCodeBundle\Event
 */
class EmailSubscriber implements EventSubscriberInterface
{
    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * EmailSubscriber constructor.
     * @param Mailer $mailer
     */
    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
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
            ReservationCreated::NAME => 'onReservationCreated'
        );
    }

    public function onReservationCreated(ReservationEvent $event)
    {
        $reservation = $event->getReservation();

        //send email to customer
        $mailConfig = array(
            'to' => $reservation->getCustomer()->getEmail(),
            'template' => 'UniqueCodeBundle:Email:customer-reservation-created.html.twig',
            'subject' => 'Confirmation de demande de réservation',
            'from' => 'krisstwo@gmail.com',//TODO: let from be empty
            'params' => array(
                'reservation' => $reservation
            )
        );
        $this->mailer->sendMessage($mailConfig, 'text/html');

        //send email to hotel
        $mailConfig = array(
            'to' => 'hotel@happybreak-codeunique.local', //TODO: hotel email
            'template' => 'UniqueCodeBundle:Email:new_reservation_request.html.twig',
            'subject' => 'Demande de réservation',
            'from' => 'krisstwo@gmail.com',//TODO: let from be empty
            'params' => array(
                'reservation' => $reservation,
                'customer' => $reservation->getCustomer(),
            )
        );
        $this->mailer->sendMessage($mailConfig, 'text/html');
    }
}