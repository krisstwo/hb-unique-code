<?php

namespace Coffeeandbrackets\UniqueCodeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EmailLog
 *
 * @ORM\Table(name="email_log")
 * @ORM\Entity(repositoryClass="Coffeeandbrackets\UniqueCodeBundle\Repository\EmailLogRepository")
 */
class EmailLog
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="`event`", type="string", length=255)
     */
    private $event;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="event_date", type="datetimetz")
     */
    private $eventDate;

    /**
     * @var string
     *
     * @ORM\Column(name="`from`", type="string", length=255)
     */
    private $from;

    /**
     * @var string
     *
     * @ORM\Column(name="`to`", type="string", length=255)
     */
    private $to;

    /**
     * @var string
     *
     * @ORM\Column(name="bcc", type="string", length=255)
     */
    private $bcc;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=255)
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text")
     */
    private $body;

    /**
     * @var
     *
     * @ORM\ManyToOne(targetEntity="Coffeeandbrackets\UniqueCodeBundle\Entity\Reservation")
     */
    private $reservation;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param string $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * Set sentDate
     *
     * @param \DateTime $eventDate
     *
     * @return EmailLog
     */
    public function setEventDate($eventDate)
    {
        $this->eventDate = $eventDate;

        return $this;
    }

    /**
     * Get sentDate
     *
     * @return \DateTime
     */
    public function getEventDate()
    {
        return $this->eventDate;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param string $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * Set recipient
     *
     * @param string $to
     *
     * @return EmailLog
     */
    public function setTo($to)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Get recipient
     *
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
     * @param string $bcc
     */
    public function setBcc($bcc)
    {
        $this->bcc = $bcc;
    }

    /**
     * Set subject
     *
     * @param string $subject
     *
     * @return EmailLog
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set body
     *
     * @param string $body
     *
     * @return EmailLog
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return mixed
     */
    public function getReservation()
    {
        return $this->reservation;
    }

    /**
     * @param mixed $reservation
     */
    public function setReservation($reservation)
    {
        $this->reservation = $reservation;
    }
}

