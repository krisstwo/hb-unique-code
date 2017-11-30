<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeBundle\Event;


use Coffeeandbrackets\UniqueCodeBundle\Event\Email\HotelConfirmationDueEmailSent;
use Coffeeandbrackets\UniqueCodeBundle\Event\Email\UnseenReservationEmailSent;
use Coffeeandbrackets\UniqueCodeBundle\Event\EmailEvent;
use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\HotelAccepted;
use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\HotelConfirmationDue;
use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\HotelDeclined;
use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\ReservationCreated;
use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\ReservationUnseen;
use Coffeeandbrackets\UniqueCodeBundle\Service\Mailer;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * EmailSubscriber constructor.
     * @param Mailer $mailer
     */
    public function __construct(Mailer $mailer, LoggerInterface $logger, EventDispatcherInterface $eventDispatcher)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
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
            ReservationCreated::NAME   => 'onReservationCreated',
            HotelDeclined::NAME        => 'onHotelDeclined',
            HotelAccepted::NAME        => 'onHotelAccepted',
            ReservationUnseen::NAME    => 'onReservationUnseen',
            HotelConfirmationDue::NAME => 'onHotelConfirmationDue',
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
            'from' => 'contact@happybreak.com',//TODO: let from be empty
            'params' => array(
                'reservation' => $reservation
            )
        );
        $this->mailer->sendMessage($mailConfig, 'text/html');

        //send email to hotel
        $mailConfig = array(
            'to' => $reservation->getHotelEmail(),
            'template' => 'UniqueCodeBundle:Email:new_reservation_request.html.twig',
            'subject' => 'Demande de réservation',
            'from' => 'contact@happybreak.com',//TODO: let from be empty
            'params' => array(
                'reservation' => $reservation,
                'customer' => $reservation->getCustomer(),
            )
        );
        $this->mailer->sendMessage($mailConfig, 'text/html');
    }

    public function onHotelDeclined(ReservationEvent $event)
    {
        $reservation = $event->getReservation();

        $mailConfig = array(
            'to'       => $reservation->getCustomer()->getEmail(),
            'template' => 'UniqueCodeBundle:Email:customer-reservation-refused.html.twig',
            'subject'  => 'Demande de réservation refusée',
            'from'     => 'contact@happybreak.com',//TODO: let from be empty
            'params'   => array(
                'reservation' => $reservation
            )
        );
        $this->mailer->sendMessage($mailConfig, 'text/html');
    }

    public function onHotelAccepted(ReservationEvent $event)
    {
        $reservation = $event->getReservation();

        //send email to hotel
        $tabParam = array(
            'to' => $reservation->getHotelEmail(),
            'template' => 'UniqueCodeBundle:Email:hotel_confirm_reservation.html.twig',
            'subject' => 'Confirmation de réservation',
            'from' => 'contact@happybreak.com',//TODO: let from be empty,
            'params' => array(
                'reservation' => $reservation
            )
        );
        $this->mailer->sendMessage($tabParam, 'text/html');

        // send confirmation mail to customer
        $tabParam = array(
            'to' => $reservation->getCustomer()->getEmail(),
            'template' => 'UniqueCodeBundle:Email:customer_confirm_reservation.html.twig',
            'subject' => 'Confirmation de votre réservation',
            'from' => 'contact@happybreak.com',//TODO: let from be empty
            'params' => array(
                'reservation' => $reservation
            )
        );
        $this->mailer->sendMessage($tabParam, 'text/html');
    }

    public function onReservationUnseen(ReservationEvent $event)
    {
        $reservation = $event->getReservation();

        //send email to office about hotel not responding
        $recipient = 'contact@happybreak.com';
        $subject   = 'Demande de réservation sans réponse';
        $tabParam  = array(
            'to'       => $recipient,
            'template' => 'UniqueCodeBundle:Email:admin-hotel-not-responding.html.twig',
            'subject'  => $subject,
            'from'     => 'contact@happybreak.com',//TODO: let from be empty,
            'params'   => array(
                'reservation' => $reservation,
                'customer'    => $reservation->getCustomer(),
            )
        );
        $this->mailer->sendMessage($tabParam, 'text/html');

        $event = new UnseenReservationEmailSent($recipient, $subject, '', $reservation);//TODO: body generation outside of mailer, or get it from the return value
        $this->eventDispatcher->dispatch(EmailEvent::NAME, $event);
    }

    public function onHotelConfirmationDue(ReservationEvent $event)
    {
        $reservation = $event->getReservation();



        if (empty($reservation->getHotelEmail())) {
            //log and quit
            $this->logger->warning(sprintf('Reservation without hotel email when notifying of confirmation due (id:%s)',
                $reservation->getId()));

            return;
        }

        //send email to office about hotel not responding
        $subject   = 'Plus que 2 heures pour accepter la réservation';
        $recipient = $reservation->getHotelEmail();
        $tabParam  = array(
            'to'       => $recipient,
            'template' => 'UniqueCodeBundle:Email:hotel-confirmation-due.html.twig',
            'subject'  => $subject,
            'from'     => 'contact@happybreak.com',//TODO: let from be empty,
            'params'   => array(
                'reservation' => $reservation,
                'customer'    => $reservation->getCustomer(),
            )
        );
        $this->mailer->sendMessage($tabParam, 'text/html');

        $event = new HotelConfirmationDueEmailSent($recipient, $subject, '', $reservation);//TODO: body generation outside of mailer, or get it from the return value
        $this->eventDispatcher->dispatch(EmailEvent::NAME, $event);
    }
}