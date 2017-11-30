<?php

namespace Coffeeandbrackets\UniqueCodeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ForfaitPlanning
 *
 * @ORM\Table(name="forfait_planning")
 * @ORM\Entity(repositoryClass="Coffeeandbrackets\UniqueCodeBundle\Repository\ForfaitPlanningRepository")
 */
class ForfaitPlanning
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="hotelName", type="string", length=255)
     */
    private $hotelName;

    /**
     * @var bool
     *
     * @ORM\Column(name="isEnabled", type="boolean")
     */
    private $isEnabled;

    /**
     * @var int
     *
     * @ORM\Column(name="forfaitInternalId", type="integer")
     */
    private $forfaitInternalId;

    /**
     * @var string
     *
     * @ORM\Column(name="year", type="string", length=4)
     */
    private $year;

    /**
     * @var string
     *
     * @ORM\Column(name="month", type="string", length=2)
     */
    private $month;

    /**
     * @var float
     *
     * @ORM\Column(name="day1", type="float", scale=2)
     */
    private $day1;

    /**
     * @var float
     *
     * @ORM\Column(name="day2", type="float", scale=2)
     */
    private $day2;

    /**
     * @var float
     *
     * @ORM\Column(name="day3", type="float", scale=2)
     */
    private $day3;

    /**
     * @var float
     *
     * @ORM\Column(name="day4", type="float", scale=2)
     */
    private $day4;

    /**
     * @var float
     *
     * @ORM\Column(name="day5", type="float", scale=2)
     */
    private $day5;

    /**
     * @var float
     *
     * @ORM\Column(name="day6", type="float", scale=2)
     */
    private $day6;

    /**
     * @var float
     *
     * @ORM\Column(name="day7", type="float", scale=2)
     */
    private $day7;

    /**
     * @var float
     *
     * @ORM\Column(name="day8", type="float", scale=2)
     */
    private $day8;

    /**
     * @var float
     *
     * @ORM\Column(name="day9", type="float", scale=2)
     */
    private $day9;

    /**
     * @var float
     *
     * @ORM\Column(name="day10", type="float", scale=2)
     */
    private $day10;

    /**
     * @var float
     *
     * @ORM\Column(name="day11", type="float", scale=2)
     */
    private $day11;

    /**
     * @var float
     *
     * @ORM\Column(name="day12", type="float", scale=2)
     */
    private $day12;

    /**
     * @var float
     *
     * @ORM\Column(name="day13", type="float", scale=2)
     */
    private $day13;

    /**
     * @var float
     *
     * @ORM\Column(name="day14", type="float", scale=2)
     */
    private $day14;

    /**
     * @var float
     *
     * @ORM\Column(name="day15", type="float", scale=2)
     */
    private $day15;

    /**
     * @var float
     *
     * @ORM\Column(name="day16", type="float", scale=2)
     */
    private $day16;

    /**
     * @var float
     *
     * @ORM\Column(name="day17", type="float", scale=2)
     */
    private $day17;

    /**
     * @var float
     *
     * @ORM\Column(name="day18", type="float", scale=2)
     */
    private $day18;

    /**
     * @var float
     *
     * @ORM\Column(name="day19", type="float", scale=2)
     */
    private $day19;

    /**
     * @var float
     *
     * @ORM\Column(name="day20", type="float", scale=2)
     */
    private $day20;

    /**
     * @var float
     *
     * @ORM\Column(name="day21", type="float", scale=2)
     */
    private $day21;

    /**
     * @var float
     *
     * @ORM\Column(name="day22", type="float", scale=2)
     */
    private $day22;

    /**
     * @var float
     *
     * @ORM\Column(name="day23", type="float", scale=2)
     */
    private $day23;

    /**
     * @var float
     *
     * @ORM\Column(name="day24", type="float", scale=2)
     */
    private $day24;

    /**
     * @var float
     *
     * @ORM\Column(name="day25", type="float", scale=2)
     */
    private $day25;

    /**
     * @var float
     *
     * @ORM\Column(name="day26", type="float", scale=2)
     */
    private $day26;

    /**
     * @var float
     *
     * @ORM\Column(name="day27", type="float", scale=2)
     */
    private $day27;

    /**
     * @var float
     *
     * @ORM\Column(name="day28", type="float", scale=2)
     */
    private $day28;

    /**
     * @var float
     *
     * @ORM\Column(name="day29", type="float", scale=2)
     */
    private $day29;

    /**
     * @var float
     *
     * @ORM\Column(name="day30", type="float", scale=2)
     */
    private $day30;

    /**
     * @var float
     *
     * @ORM\Column(name="day31", type="float", scale=2)
     */
    private $day31;

    /**
     * @var string
     *
     * @ORM\Column(name="service_afternoon", type="string", length=255)
     */
    private $serviceAfternoon;

    /**
     * @var string
     *
     * @ORM\Column(name="service_night", type="string", length=255)
     */
    private $serviceNight;

    /**
     * @var string
     *
     * @ORM\Column(name="service_morning", type="string", length=255)
     */
    private $serviceMorning;


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
     * Set name
     *
     * @param string $name
     *
     * @return ForfaitPlanning
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set hotelName
     *
     * @param string $hotelName
     *
     * @return ForfaitPlanning
     */
    public function setHotelName($hotelName)
    {
        $this->hotelName = $hotelName;

        return $this;
    }

    /**
     * Get hotelName
     *
     * @return string
     */
    public function getHotelName()
    {
        return $this->hotelName;
    }

    /**
     * Set isEnabled
     *
     * @param boolean $isEnabled
     *
     * @return ForfaitPlanning
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    /**
     * Get isEnabled
     *
     * @return bool
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * Set forfaitInternalId
     *
     * @param integer $forfaitInternalId
     *
     * @return ForfaitPlanning
     */
    public function setForfaitInternalId($forfaitInternalId)
    {
        $this->forfaitInternalId = $forfaitInternalId;

        return $this;
    }

    /**
     * Get forfaitInternalId
     *
     * @return int
     */
    public function getForfaitInternalId()
    {
        return $this->forfaitInternalId;
    }

    /**
     * Set year
     *
     * @param string $year
     *
     * @return ForfaitPlanning
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return string
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set month
     *
     * @param string $month
     *
     * @return ForfaitPlanning
     */
    public function setMonth($month)
    {
        $this->month = $month;

        return $this;
    }

    /**
     * Get month
     *
     * @return string
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Get days as an array day => price
     *
     * @return array
     */
    public function getDaysArray()
    {
        $days = array();
        for ($i = 1; $i <= 31; $i++) {
            if (property_exists($this, 'day' . $i)) {
                $days[$i] = $this->{'day' . $i};
            }
        }

        return $days;
    }

    /**
     * @return string
     */
    public function getServiceAfternoon()
    {
        return $this->serviceAfternoon;
    }

    /**
     * @param string $serviceAfternoon
     */
    public function setServiceAfternoon($serviceAfternoon)
    {
        $this->serviceAfternoon = $serviceAfternoon;
    }

    /**
     * @return string
     */
    public function getServiceNight()
    {
        return $this->serviceNight;
    }

    /**
     * @param string $serviceNight
     */
    public function setServiceNight($serviceNight)
    {
        $this->serviceNight = $serviceNight;
    }

    /**
     * @return string
     */
    public function getServiceMorning()
    {
        return $this->serviceMorning;
    }

    /**
     * @param string $serviceMorning
     */
    public function setServiceMorning($serviceMorning)
    {
        $this->serviceMorning = $serviceMorning;
    }
}

