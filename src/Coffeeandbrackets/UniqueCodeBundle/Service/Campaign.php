<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeBundle\Service;


use Doctrine\ORM\EntityManager;

class Campaign
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Campaign constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function detectCampaign()
    {
        $campaign = $this->em->getRepository('UniqueCodeBundle:Campaign')->find(1);

        return $campaign;
    }
}