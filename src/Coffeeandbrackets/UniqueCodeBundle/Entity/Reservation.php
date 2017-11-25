<?php

namespace Coffeeandbrackets\UniqueCodeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Reservation
 *
 * @ORM\Table(name="reservation")
 * @ORM\Entity(repositoryClass="Coffeeandbrackets\UniqueCodeBundle\Repository\ReservationRepository") @ORM\HasLifecycleCallbacks
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
     * @ORM\Column(name="hotel_email", type="string", length=255)
     */
    private $hotelEmail;

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
     * @ORM\Column(name="update_date", type="datetime", nullable=true)
     */
    private $updateDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="hotel_confirmation_date", type="datetime", nullable=true)
     */
    private $hotelConfirmationDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="hotel_refuse_date", type="datetime", nullable=true)
     */
    private $hotelRefuseDate;

    /**
     * @var string
     *
     * @ORM\Column(name="hotel_refuse_reason", type="text", nullable=true)
     */
    private $hotelRefuseReason;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="hotel_proposed_check_in_date", type="date", nullable=true)
     */
    private $hotelProposedCheckInDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="hotel_proposed_check_out_date", type="date", nullable=true)
     */
    private $hotelProposedCheckOutDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="customer_acceptance_date", type="datetime", nullable=true)
     */
    private $customerAcceptanceDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="customer_decline_date", type="datetime", nullable=true)
     */
    private $customerDeclineDate;

    /**
     * @var
     *
     * @ORM\ManyToOne(targetEntity="Coffeeandbrackets\UniqueCodeBundle\Entity\Customer", cascade={"persist"})
     */
    private $customer;

    /**
     * @var
     *
     * @ORM\ManyToOne(targetEntity="Coffeeandbrackets\UniqueCodeBundle\Entity\Campaign")
     * @ORM\JoinColumn(name="campaign_id", referencedColumnName="cam_id")
     */
    private $campaign;

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->addDate = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updateDate = new \DateTime();
    }

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
     * @return string
     */
    public function getHotelEmail()
    {
        return $this->hotelEmail;
    }

    /**
     * @param string $hotelEmail
     */
    public function setHotelEmail($hotelEmail)
    {
        $this->hotelEmail = $hotelEmail;
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
     * Get addDate
     *
     * @return \DateTime
     */
    public function getAddDate()
    {
        return $this->addDate;
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
     * @return Customer
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
     * @return mixed
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @param mixed $campaign
     */
    public function setCampaign($campaign)
    {
        $this->campaign = $campaign;
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

    /**
     * @return \DateTime
     */
    public function getHotelConfirmationDate()
    {
        return $this->hotelConfirmationDate;
    }

    /**
     * @param \DateTime $hotelConfirmationDate
     */
    public function setHotelConfirmationDate($hotelConfirmationDate)
    {
        $this->hotelConfirmationDate = $hotelConfirmationDate;
    }

    /**
     * @return \DateTime
     */
    public function getHotelRefuseDate()
    {
        return $this->hotelRefuseDate;
    }

    /**
     * @param \DateTime $hotelRefuseDate
     */
    public function setHotelRefuseDate($hotelRefuseDate)
    {
        $this->hotelRefuseDate = $hotelRefuseDate;
    }

    /**
     * @return string
     */
    public function getHotelRefuseReason()
    {
        return $this->hotelRefuseReason;
    }

    /**
     * @param string $hotelRefuseReason
     */
    public function setHotelRefuseReason($hotelRefuseReason)
    {
        $this->hotelRefuseReason = $hotelRefuseReason;
    }

    /**
     * @return \DateTime
     */
    public function getHotelProposedCheckInDate()
    {
        return $this->hotelProposedCheckInDate;
    }

    /**
     * @param \DateTime $hotelProposedCheckInDate
     */
    public function setHotelProposedCheckInDate($hotelProposedCheckInDate)
    {
        $this->hotelProposedCheckInDate = $hotelProposedCheckInDate;
    }

    /**
     * @return \DateTime
     */
    public function getHotelProposedCheckOutDate()
    {
        return $this->hotelProposedCheckOutDate;
    }

    /**
     * @param \DateTime $hotelProposedCheckOutDate
     */
    public function setHotelProposedCheckOutDate($hotelProposedCheckOutDate)
    {
        $this->hotelProposedCheckOutDate = $hotelProposedCheckOutDate;
    }

    /**
     * @return \DateTime
     */
    public function getCustomerAcceptanceDate()
    {
        return $this->customerAcceptanceDate;
    }

    /**
     * @param \DateTime $customerAcceptanceDate
     */
    public function setCustomerAcceptanceDate($customerAcceptanceDate)
    {
        $this->customerAcceptanceDate = $customerAcceptanceDate;
    }

    /**
     * @return \DateTime
     */
    public function getCustomerDeclineDate()
    {
        return $this->customerDeclineDate;
    }

    /**
     * @param \DateTime $customerDeclineDate
     */
    public function setCustomerDeclineDate($customerDeclineDate)
    {
        $this->customerDeclineDate = $customerDeclineDate;
    }
}

