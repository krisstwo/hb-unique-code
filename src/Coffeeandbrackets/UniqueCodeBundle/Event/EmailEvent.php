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
    protected $from;

    /**
     * @var string
     */
    protected $to;

    /**
     * @var string
     */
    protected $bcc;

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
     * @param string $from
     * @param string $to
     * @param string $bcc
     * @param string $subject
     * @param string $body
     * @param Reservation $reservation
     */
    public function __construct($from, $to, $bcc, $subject, $body, Reservation $reservation)
    {
        $this->from          = $from;
        $this->to          = $to;
        $this->bcc          = $bcc;
        $this->subject     = $subject;
        $this->body        = $body;
        $this->reservation = $reservation;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return string
     */
    public function getBcc()
    {
        return $this->bcc;
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