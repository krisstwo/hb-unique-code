<?php

namespace Coffeeandbrackets\UniqueCodeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CodeUniqueHandleUnansweredHotelPropositionCommand extends ContainerAwareCommand {
    protected function configure() {
        $this
            ->setName('code-unique:handle-unanswered-hotel-proposition')
            ->setDescription('Decline hotel proposition automatically after 72h');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $unansweredHotelPropositions = $this->getContainer()->get('doctrine')->getEntityManager()->getRepository('UniqueCodeBundle:Reservation')->findUnansweredHotelPropositions();
        /**
         * @var $reservationService Reservation
         */
        $reservationService = $this->getContainer()->get('unique_code.reservation');

        foreach ($unansweredHotelPropositions as $reservation){
            $reservationService->autoCustomerDeclineHotelProposing($reservation);
        }

        $output->writeln('Command ended.');
    }
}