<?php

namespace Coffeeandbrackets\UniqueCodeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Reservation
 *
 * @ORM\Table(name="reservation")
 * @ORM\Entity(repositoryClass="Coffeeandbrackets\UniqueCodeBundle\Repository\ReservationRepository")
 */
class Reservation
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
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;

    /**
     * @var int
     *
     * @ORM\Column(name="number_person", type="integer")
     */
    private $numberPerson;

    /**
     * @var string
     *
     * @ORM\Column(name="hotel", type="string", length=255)
     */
    private $hotel;

    /**
     * @var string
     *
     * @ORM\Column(name="offer", type="string", length=255)
     */
    private $offer;
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="reservation_date", type="datetime")
     */
    private $reservationDate;

    /**
     * @var int
     *
     * @ORM\Column(name="number_night", type="integer")
     */
    private $numberNight;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_msg", type="string", length=255)
     */
    private $customerMsg;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="add_date", type="datetime")
     */
    private $addDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_date", type="datetime")
     */
    private $updateDate;

    /**
     * @var
     *
     * @ORM\OneToOne(targetEntity="Coffeeandbrackets\UniqueCodeBundle\Entity\Customer", cascade={"persist"})
     */
    private $customer;

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
     * Set code
     *
     * @param string $code
     *
     * @return Reservation
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set numberPerson
     *
     * @param integer $numberPerson
     *
     * @return Reservation
     */
    public function setNumberPerson($numberPerson)
    {
        $this->numberPerson = $numberPerson;

        return $this;
    }

    /**
     * Get numberPerson
     *
     * @return int
     */
    public function getNumberPerson()
    {
        return $this->numberPerson;
    }

    /**
     * Set hotel
     *
     * @param string $hotel
     *
     * @return Reservation
     */
    public function setHotel($hotel)
    {
        $this->hotel = $hotel;

        return $this;
    }

    /**
     * Get hotel
     *
     * @return string
     */
    public function getHotel()
    {
        return $this->hotel;
    }

    /**
     * Set reservationDate
     *
     * @param \DateTime $reservationDate
     *
     * @return Reservation
     */
    public function setReservationDate($reservationDate)
    {
        $this->reservationDate = $reservationDate;

        return $this;
    }

    /**
     * Get reservationDate
     *
     * @return \DateTime
     */
    public function getReservationDate()
    {
        return $this->reservationDate;
    }

    /**
     * Set numberNight
     *
     * @param integer $numberNight
     *
     * @return Reservation
     */
    public function setNumberNight($numberNight)
    {
        $this->numberNight = $numberNight;

        return $this;
    }

    /**
     * Get numberNight
     *
     * @return int
     */
    public function getNumberNight()
    {
        return $this->numberNight;
    }

    /**
     * Set customerMsg
     *
     * @param string $customerMsg
     *
     * @return Reservation
     */
    public function setCustomerMsg($customerMsg)
    {
        $this->customerMsg = $customerMsg;

        return $this;
    }

    /**
     * Get customerMsg
     *
     * @return string
     */
    public function getCustomerMsg()
    {
        return $this->customerMsg;
    }

    /**
     * Set addDate
     *
     * @param \DateTime $addDate
     *
     * @return Reservation
     */
    public function setAddDate($addDate)
    {
        $this->addDate = $addDate;

        return $this;
    }

    /**
     * Get addDate
     *
     * @return \DateTime
     */
    public function getAddDate()
    {
        return $this->addDate;
    }

    /**
     * Set updateDate
     *
     * @param \DateTime $updateDate
     *
     * @return Reservation
     */
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    /**
     * Get updateDate
     *
     * @return \DateTime
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * @return mixed
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param mixed $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return string
     */
    public function getOffer()
    {
        return $this->offer;
    }

    /**
     * @param string $offer
     */
    public function setOffer($offer)
    {
        $this->offer = $offer;
    }
}

