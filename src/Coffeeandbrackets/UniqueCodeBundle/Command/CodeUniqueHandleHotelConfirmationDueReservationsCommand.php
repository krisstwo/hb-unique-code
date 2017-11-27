<?php

namespace Coffeeandbrackets\UniqueCodeBundle\Command;

use Coffeeandbrackets\UniqueCodeBundle\Service\Reservation;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CodeUniqueHandleHotelConfirmationDueReservationsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('code-unique:handle-hotel-confirmation-due-reservations')
            ->setDescription('Send messages about unseen reservations');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $hotelConfirmationDueReservations = $this->getContainer()->get('doctrine')->getEntityManager()->getRepository('UniqueCodeBundle:Reservation')->findHotelConfirmationDue();
        /**
         * @var $reservationService Reservation
         */
        $reservationService = $this->getContainer()->get('unique_code.reservation');

        foreach ($hotelConfirmationDueReservations as $reservation){
            $reservationService->hotelConfirmationDueReservation($reservation);
        }

        $output->writeln('Command ended.');
    }

}
