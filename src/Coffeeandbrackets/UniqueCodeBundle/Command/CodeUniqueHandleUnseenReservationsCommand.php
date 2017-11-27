<?php

namespace Coffeeandbrackets\UniqueCodeBundle\Command;

use Coffeeandbrackets\UniqueCodeBundle\Service\Reservation;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CodeUniqueHandleUnseenReservationsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('code-unique:handle-unseen-reservations')
            ->setDescription('Send messages about unseen reservations');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $unseenReservations = $this->getContainer()->get('doctrine')->getEntityManager()->getRepository('UniqueCodeBundle:Reservation')->findUnseen();
        /**
         * @var $reservationService Reservation
         */
        $reservationService = $this->getContainer()->get('unique_code.reservation');

        foreach ($unseenReservations as $reservation){
            $reservationService->unseenReservation($reservation);
        }

        $output->writeln('Command ended.');
    }

}
