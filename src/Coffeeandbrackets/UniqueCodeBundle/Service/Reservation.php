<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeBundle\Service;

use Coffeeandbrackets\UniqueCodeBundle\Entity\Customer;
use Coffeeandbrackets\UniqueCodeBundle\Entity\Reservation as ReservationEntity;
use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\CodeActivated;
use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\CustomerAccepted;
use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\CustomerDeclined;
use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\HotelAccepted;
use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\HotelConfirmationDue;
use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\HotelDeclined;
use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\ReservationCreated;
use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\ReservationUnseen;
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

    /**
     * @var Campaign
     */
    private $campaignService;

    /**
     * @var Hotels
     */
    private $hotelsService;

    public function __construct(
        EntityManager $entityManager,
        EventDispatcherInterface $eventDispatcher,
        Campaign $campaignService,
        Hotels $hotelsService
    )
    {
        $this->em = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->campaignService = $campaignService;
        $this->hotelsService = $hotelsService;
    }

    public function activateCode($code)
    {
        $campaign = $this->campaignService->detectCampaign();

        //dumb reservation for the event interface
        $emptyReservation = new ReservationEntity();
        $emptyReservation->setCode($code);
        $emptyReservation->setCampaign($campaign);

        $event = new CodeActivated($emptyReservation);
        $this->eventDispatcher->dispatch(CodeActivated::NAME, $event);
    }

    public function createReservation($data)
    {
        $campaign = $this->campaignService->detectCampaign();

        $customer = new Customer();
        $customer->setFirstName($data['first_name']);
        $customer->setLastName($data['last_name']);
        $customer->setEmail($data['email']);
        $customer->setPhone($data['phone']);
        $customer->setAcceptNewsletter(isset($data['newsletter']) ? true : false);
        $customer->setCampaign($campaign);

        $reservation = new ReservationEntity();
        $reservation->setCode($data['code']);
        $reservation->setReservationDate(date_create_from_format('d/m/Y', $data['date']));
        $reservation->setNumberNight($data['number_night']);
        $reservation->setNumberPerson($data['number_person']);

        //Hotel validated in form, can use label directly
        $reservation->setHotel($data['hotel-name']);

        $hotel = $this->hotelsService->findOneByNameId($data['hotel-name'], $data['hotel']);
        if ($hotel) {
            $reservation->setHotelEmail(iconv("UTF-8", "ASCII//IGNORE", $hotel['email']));
        }

        $reservation->setHotelPhone($data['hotel_phone']);
        $reservation->setHotelAddress($data['hotel_address']);

        $reservation->setOffer($data['offer-name']);

        $reservation->setCustomerMsg($data['customer_msg']);
        $reservation->setCustomer($customer);
        $reservation->setCampaign($campaign);

        $reservation->setOfferServiceAfternoon($data['offer_service_afternoon']);
        $reservation->setOfferServiceNight($data['offer_service_night']);
        $reservation->setOfferServiceMorning($data['offer_service_morning']);

        $reservation->setOfferPrice($data['offer_price']);

        $this->em->persist($reservation);
        $this->em->flush();

        $event = new ReservationCreated($reservation);
        $this->eventDispatcher->dispatch(ReservationCreated::NAME, $event);
    }

    public function hotelRefuseReservation(ReservationEntity $reservation, $data)
    {
        try {
            $reservation->setHotelRefuseDate(new \DateTime());
            $reservation->setHotelRefuseReason($data['reason']);
            $reservation->setHotelProposedCheckInDate(date_create_from_format('d/m/Y', $data['check-in-date']));
            $reservation->setHotelProposedNumberNight($data['nights']);

            //dispatch event
            $event = new HotelDeclined($reservation);
            $this->eventDispatcher->dispatch(HotelDeclined::NAME, $event);

            $this->em->persist($reservation);
            $this->em->flush();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function hotelAcceptReservation(ReservationEntity $reservation)
    {
        $reservation->setHotelConfirmationDate(new \DateTime());

        //dispatch event
        $event = new HotelAccepted($reservation);
        $this->eventDispatcher->dispatch(HotelAccepted::NAME, $event);

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

            //create a new reservation with the proposed and accepted dates
            $newReservation = new ReservationEntity();
            $newReservation->setCode($reservation->getCode());
            $newReservation->setReservationDate($reservation->getHotelProposedCheckInDate());
            $newReservation->setNumberNight($reservation->getHotelProposedNumberNight());
            $newReservation->setNumberPerson($reservation->getNumberPerson());
            $newReservation->setHotel($reservation->getHotel());
            $newReservation->setHotelAddress($reservation->getHotelAddress());
            $newReservation->setHotelPhone($reservation->getHotelPhone());
            $newReservation->setOffer($reservation->getOffer());
            $newReservation->setHotelEmail($reservation->getHotelEmail());
            $newReservation->setCustomerMsg($reservation->getCustomerMsg());
            $newReservation->setCustomer($reservation->getCustomer());
            $newReservation->setCampaign($reservation->getCampaign());
            $newReservation->setOfferServiceAfternoon($reservation->getOfferServiceAfternoon());
            $newReservation->setOfferServiceNight($reservation->getOfferServiceNight());
            $newReservation->setOfferServiceMorning($reservation->getOfferServiceMorning());
            $newReservation->setOfferPrice($reservation->getOfferPrice());
            $newReservation->setHotelConfirmationDate($reservation->HotelRefuseDate());

            //TODO: must move to the end to simulate a transaction ...
            $this->em->persist($newReservation);
            $this->em->flush();

            //dispatch acceptance event
            $event = new CustomerAccepted($newReservation);
            $this->eventDispatcher->dispatch(CustomerAccepted::NAME, $event);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function unseenReservation(ReservationEntity $reservation)
    {
        $event = new ReservationUnseen($reservation);
        $this->eventDispatcher->dispatch(ReservationUnseen::NAME, $event);
    }

    public function hotelConfirmationDueReservation(ReservationEntity $reservation)
    {
        $event = new HotelConfirmationDue($reservation);
        $this->eventDispatcher->dispatch(HotelConfirmationDue::NAME, $event);
    }
}