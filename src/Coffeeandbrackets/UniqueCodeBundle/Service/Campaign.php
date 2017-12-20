<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeBundle\Service;


use Coffeeandbrackets\UniqueCodeBundle\Entity\Reservation as ReservationEntity;
use Coffeeandbrackets\UniqueCodeBundle\Entity\Campaign as CampaignEntity;
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

    private $compatibleRoutes = array(
        'unique_code_view_reservation',
        'unique_code_accept_reservation',
        'unique_code_hotel_refuse_reservation',
        'unique_code_customer_reservation_action',
        'unique_code_customer_reservation_action',
    );

    /**
     * Campaign constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em, RequestStack $requestStack)
    {
        $this->em           = $em;
        $this->requestStack = $requestStack;
    }

    public function detectCampaign()
    {
        $campaign = null;
        $request  = $this->requestStack->getMasterRequest();

        $code          = $request->get('campaignCode');
        $reservationId = $request->get('id');
        $route         = $request->get('_route');

        if ( ! empty($code)) {
            $campaign = $this->em->getRepository('UniqueCodeBundle:Campaign')->findOneBy(array('code' => $code));
        } elseif (in_array($route, $this->compatibleRoutes) && ! empty($reservationId)) {
            /**
             * @var $reservation Reservation
             */
            $reservation = $this->em->getRepository('UniqueCodeBundle:Reservation')->find($reservationId);
            $campaign    = $reservation->getCampaign();
        }

        return $campaign;
    }

    /**
     * @param CampaignEntity $campaign
     * @return int|mixed
     */
    public function getLastClearSequenceCode(CampaignEntity $campaign = null){
        $clearSequenceLastValue = 0;
        if($campaign == null){
            $clearSequenceLastValue = $this->em->getRepository('UniqueCodeBundle:Code')->getLastClearNoCampaign();
        }else {
            $clearSequenceLastValue = $this->em->getRepository('UniqueCodeBundle:Code')->getLastClearByCampaign($campaign);
        }

        return $clearSequenceLastValue;
    }
}