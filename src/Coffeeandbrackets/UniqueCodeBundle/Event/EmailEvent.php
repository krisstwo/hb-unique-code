<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeBundle\Event;


use Coffeeandbrackets\UniqueCodeBundle\Entity\Reservation;
use Symfony\Component\EventDispatcher\Event;

abstract class EmailEvent extends Event
{
    const NAME = 'email';

    /**
     * @var string
     */
    protected $recipient;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $body;

    /**
     * @var Reservation
     */
    protected $reservation;

    /**
     * EmailEvent constructor.
     *
     * @param string $recipient
     * @param string $subject
     * @param string $body
     * @param Reservation $reservation
     */
    public function __construct($recipient, $subject, $body, Reservation $reservation)
    {
        $this->recipient   = $recipient;
        $this->subject     = $subject;
        $this->body        = $body;
        $this->reservation = $reservation;
    }

    /**
     * @return string
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }


    /**
     * @return Reservation
     */
    public function getReservation()
    {
        return $this->reservation;
    }
}