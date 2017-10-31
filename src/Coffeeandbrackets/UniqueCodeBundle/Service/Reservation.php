<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeBundle\Service;


use Coffeeandbrackets\UniqueCodeBundle\Entity\Reservation as ReservationEntity;
use Doctrine\ORM\EntityManager;

class Reservation
{
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function hotelRefuseReservation(ReservationEntity $reservation, $data)
    {
        $reservation->setHotelRefuseDate(new \DateTime());
        $reservation->setHotelRefuseReason($data['reason']);
        $reservation->setHotelProposedCheckInDate(date_create_from_format('d/m/Y', $data['check-in-date']));
        $reservation->setHotelProposedCheckOutDate(date_create_from_format('d/m/Y', $data['check-in-date'])->add(new \DateInterval(sprintf('P%dD', $data['nights']))));
        $this->em->persist($reservation);
        $this->em->flush();
    }
}