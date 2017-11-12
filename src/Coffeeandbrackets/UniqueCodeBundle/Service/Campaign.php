<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeBundle\Service;


use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RequestStack;

class Campaign
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * Campaign constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, RequestStack $requestStack)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
    }

    public function detectCampaign()
    {

        $request = $this->requestStack->getMasterRequest();
        $code = $request->get('campaignCode');
        if(empty($code))
            return null;

        $campaign = $this->em->getRepository('UniqueCodeBundle:Campaign')->findOneBy(array('code' => $code));

        return $campaign;
    }
}