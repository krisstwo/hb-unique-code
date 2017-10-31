<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeBundle\Service;


use Coffeeandbrackets\UniqueCodeBundle\Entity\Reservation as ReservationEntity;
use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\CustomerAccepted;
use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\CustomerDeclined;
use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\ReservationCreated;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Reservation
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EntityManager $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->em = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
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

    public function customerDeclineHotelProposing(ReservationEntity $reservation)
    {
        try {
            $reservation->setCustomerDeclineDate(new \DateTime());

            //dispatch event
            $event = new CustomerDeclined($reservation);
            $this->eventDispatcher->dispatch(CustomerDeclined::NAME, $event);

            $this->em->flush();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function customerAcceptHotelProposing(ReservationEntity $reservation)
    {
        try {
            $reservation->setCustomerAcceptanceDate(new \DateTime());

            //dispatch acceptance event
            $event = new CustomerAccepted($reservation);
            $this->eventDispatcher->dispatch(CustomerAccepted::NAME, $event);

            //create a new reservation with the proposed and accepted dates
            $newReservation = new ReservationEntity();
            $newReservation->setCode($reservation->getCode());
            $newReservation->setReservationDate($reservation->getHotelProposedCheckInDate());
            $newReservation->setNumberNight($reservation->getHotelProposedCheckInDate()->diff($reservation->getHotelProposedCheckOutDate())->format('%a'));
            $newReservation->setNumberPerson($reservation->getNumberPerson());
            $newReservation->setHotel($reservation->getHotel());
            $newReservation->setOffer($reservation->getOffer());
            $newReservation->setCustomerMsg($reservation->getCustomerMsg());
            $newReservation->setCustomer($reservation->getCustomer());

            //TODO: must move to the end to simulate a transaction ...
            $this->em->persist($newReservation);
            $this->em->flush();

            //new reservation event
            $event = new ReservationCreated($newReservation);
            $this->eventDispatcher->dispatch(ReservationCreated::NAME, $event);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}