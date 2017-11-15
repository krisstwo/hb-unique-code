<?php

namespace Coffeeandbrackets\UniqueCodeBundle\Controller;

use Coffeeandbrackets\UniqueCodeBundle\Entity\Customer;
use Coffeeandbrackets\UniqueCodeBundle\Entity\Reservation;
use Coffeeandbrackets\UniqueCodeBundle\Form\HotelRefuseReservation;
use Coffeeandbrackets\UniqueCodeBundle\Service\Campaign;
use Coffeeandbrackets\UniqueCodeBundle\Service\CheckCode;
use Coffeeandbrackets\UniqueCodeBundle\Service\Hotels;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function headerCampaignLogoAction()
    {
        /**
         * @var $campaignService Campaign
         */
        $campaignService = $this->get('unique_code.campaign');

        return $this->render('UniqueCodeBundle:Default:partials/header-campaign-logo.html.twig',
            array(
                'campaign' => $campaignService->detectCampaign()
            )
        );
    }

    public function indexAction()
    {
        /**
         * @var $campaignService Campaign
         */
        $campaignService = $this->get('unique_code.campaign');

        return $this->render('UniqueCodeBundle:Default:index.html.twig',
            array(
                'campaign' => $campaignService->detectCampaign()
            )
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function submitCustomerInformationAction(Request $request)
    {

        if ($request->isXmlHttpRequest()) {
            $code = $request->get('code');
            /**
             * @var $checker CheckCode
             */
            $checker = $this->get('unique_code.check_code');

            switch ($checker->validate($code)) {
                case CheckCode::INVALID_CODE_NOT_FOUND:
                    return new JsonResponse(array('error' => 'Le code unique indiqué n\'est pas valide.'));
                    break;
                case CheckCode::INVALID_CODE_USED:
                    return new JsonResponse(array('error' => 'Le code unique indiqué a déjà été utilisé.'));
                    break;
                case CheckCode::INVALID_CODE_RESERVED:
                    return new JsonResponse(array('error' => 'Le code unique indiqué a déjà une demande de reservation en cours. Vous ne pouvez envoyer plusieurs demandes de réservation en même temps.'));
                    break;
                default:
                    return new JsonResponse(array());
                    break;
            }
        }

        return new Response("Action not allowed", 400);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function ajaxSearchHotelAction(Request $request)
    {
        $query = $request->get('q');

        /**
         * @var $hotelsService Hotels
         */
        $hotelsService = $this->get('unique_code.hotels');

        return new JsonResponse($hotelsService->find($query));
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function submitReservationAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            /**
             * @var $campaignService Campaign
             */
            $campaignService = $this->get('unique_code.campaign');
            $campaign = $campaignService->detectCampaign();

            /**
             * @var $hotelsService Hotels
             */
            $hotelsService = $this->get('unique_code.hotels');

            //TODO validation data

            $customer = new Customer();
            $customer->setFirstName($request->get('first_name'));
            $customer->setLastName($request->get('last_name'));
            $customer->setEmail($request->get('email'));
            $customer->setAcceptNewsletter(false);
            $customer->setCampaign($campaign);

            $reservation = new Reservation();
            $reservation->setCode($request->get('code'));
            $reservation->setReservationDate(date_create_from_format('d/m/Y', $request->get('date')));
            $reservation->setNumberNight($request->get('number_night'));
            $reservation->setNumberPerson($request->get('number_person'));

            $hotels  = $hotelsService->find($request->get('hotel-name'));
            $hotelId = $request->get('hotel');
            if ( ! isset($hotels[$hotelId])) {
                return new JsonResponse(array('error' => 'Hôtel invalide'));
            }
            $reservation->setHotel($hotels[$hotelId]['label']);

            $formulaId = $request->get('offer');
            if ( ! isset($hotels[$hotelId]['formulas'][$formulaId])) {
                return new JsonResponse(array('error' => 'Formule invalide'));
            }
            $reservation->setOffer($hotels[$hotelId]['formulas'][$formulaId]['label']);

            $reservation->setCustomerMsg($request->get('customer_msg'));
            $reservation->setCustomer($customer);
            $reservation->setCampaign($campaign);

            $em = $this->getDoctrine()->getManager();
            $em->persist($reservation);

            $code = $em->getRepository('UniqueCodeBundle:Code')->findOneBy(['code' => $request->get('code')]);

            $workflow = $this->get('workflow.status_code');
            if ($workflow->can($code, 'request')) {
                $workflow->apply($code, 'request');
            }

            $em->flush();

            // TODO get hotel email from extern BD
            // Send mail to hotel
            $serviceMail = $this->container->get('unique_code.mailer');
            $tabParam    = array(
                'to'       => 'hotel@hotel.com',
                'template' => 'UniqueCodeBundle:Email:new_reservation_request.html.twig',
                'subject'  => 'Demande de réservation',
                'from'     => array($this->container->getParameter('mailer_user') => 'HappyBreak'),
                'params'   => array(
                    'customer'    => $customer,
                    'reservation' => $reservation
                )
            );

            // send mail
            $serviceMail->sendMessage($tabParam, 'text/html');

            //send email to customer
            $mailConfig = array(
                'to'       => $reservation->getCustomer()->getEmail(),
                'template' => 'UniqueCodeBundle:Email:customer-reservation-created.html.twig',
                'subject'  => 'Confirmation de demande de réservation',
                'from'     => array($this->container->getParameter('mailer_user') => 'HappyBreak'),//TODO: let from be empty
                'params'   => array(
                    'reservation' => $reservation
                )
            );
            $serviceMail->sendMessage($mailConfig, 'text/html');

            return new JsonResponse(array());
        }

        return new Response("Action not allowed", 400);
    }

    public function viewReservationAction($id) {
        $reservation = $this->getDoctrine()->getRepository('UniqueCodeBundle:Reservation')->find($id);
        return $this->render('UniqueCodeBundle:Default:view_reservation.html.twig',
            array('reservation' => $reservation));
    }

    public function acceptReservationAction($id) {
        $reservation = $this->getDoctrine()->getRepository('UniqueCodeBundle:Reservation')->find($id);

        $serviceMail = $this->container->get('unique_code.mailer');
        // send confirmation mail to hotel
        $tabParam = array(
            'to' => 'hotel@hotel.com',
            'template' => 'UniqueCodeBundle:Email:hotel_confirm_reservation.html.twig',
            'subject' => 'Confirmation de réservation',
            'from' => array($this->container->getParameter('mailer_user') => 'HappyBreak'),
            'params' => array(
                'reservation' => $reservation
            )
        );
        $serviceMail->sendMessage($tabParam, 'text/html');

        // send confirmation mail to customer
        $tabParam = array(
            'to' => $reservation->getCustomer()->getEmail(),
            'template' => 'UniqueCodeBundle:Email:customer_confirm_reservation.html.twig',
            'subject' => 'Confirmation de votre réservation',
            'from' => array($this->container->getParameter('mailer_user') => 'HappyBreak'),
            'params' => array(
                'reservation' => $reservation
            )
        );
        $serviceMail->sendMessage($tabParam, 'text/html');

        $code = $this->getDoctrine()->getRepository('UniqueCodeBundle:Code')->findOneBy(['code' => $reservation->getCode()]);
        $workflow = $this->get('workflow.status_code');
        if($workflow->can($code, 'accept')){
            $workflow->apply($code, 'accept');
        }
        $this->getDoctrine()->getManager()->flush();

        return $this->render('UniqueCodeBundle:Default:thanks_confirm_reservation.html.twig',
            array('reservation' => $reservation));
    }

    public function hotelRefuseReservationAction(Request $request)
    {
        $id = $request->get('id');

        /**
         * @var Reservation $reservation
         */
        $reservation = $this->getDoctrine()->getRepository('UniqueCodeBundle:Reservation')->find($id);

        //validate reservation state before proceeding
        if ($reservation->getHotelConfirmationDate()
            || $reservation->getHotelRefuseDate()
            || $reservation->getCustomerAcceptanceDate()) {
            $this->addFlash(
                'error',
                'Cette réservation n\'est pas dans un status adéquat'
            );

            return $this->redirectToRoute('unique_code_homepage');
        }


        return $this->render('UniqueCodeBundle:Default:hotel-refuse-reservation.html.twig',
            array('reservation' => $reservation));
    }

    public function postHotelRefuseReservationAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $id = $request->get('id');

            /**
             * @var Reservation $reservation
             */
            $reservation = $this->getDoctrine()->getRepository('UniqueCodeBundle:Reservation')->find($id);

            //validate reservation state before proceeding
            if ($reservation->getHotelConfirmationDate()
                || $reservation->getHotelRefuseDate()
                || $reservation->getCustomerAcceptanceDate()) {

                return new JsonResponse(array('error' => 'Cette réservation n\'est pas dans un status adéquat'));
            }

            /**
             * @var Form $form
             */
            $form = $this->get('form.factory')->createNamedBuilder('', HotelRefuseReservation::class)->getForm();
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                /**
                 * @var $reservationService \Coffeeandbrackets\UniqueCodeBundle\Service\Reservation
                 */
                $reservationService = $this->get('unique_code.reservation');
                $reservationService->hotelRefuseReservation($reservation,$data);

                $serviceMail = $this->container->get('unique_code.mailer');
                $tabParam = array(
                    'to' => $reservation->getCustomer()->getEmail(),
                    'template' => 'UniqueCodeBundle:Email:customer-reservation-refused.html.twig',
                    'subject' => 'Demande de réservation refusée',
                    'from' => array($this->container->getParameter('mailer_user') => 'HappyBreak'),
                    'params' => array(
                        'reservation' => $reservation
                    )
                );
                $serviceMail->sendMessage($tabParam, 'text/html');

                return new JsonResponse(array());
            }else {
                return new JsonResponse(array('error' => 'Données invalides'));
            }
        }
        return new Response("Action not allowed", 400);
    }

    public function customerReservationActionAction(Request $request)
    {
        $id = $request->get('id');

        /**
         * @var Reservation $reservation
         */
        $reservation = $this->getDoctrine()->getRepository('UniqueCodeBundle:Reservation')->find($id);

        //validate reservation state before proceeding
        if ($reservation->getHotelConfirmationDate()
            || !$reservation->getHotelRefuseDate()) {
            $this->addFlash(
                'error',
                'Cette réservation n\'est pas dans un status adéquat'
            );

            return $this->redirectToRoute('unique_code_homepage');
        }

        return $this->render('UniqueCodeBundle:Default:customer-reservation-action.html.twig',
            array('reservation' => $reservation));
    }

    public function ajaxCustomerAcceptHotelProposingAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $id = $request->get('id');

            /**
             * @var Reservation $reservation
             */
            $reservation = $this->getDoctrine()->getRepository('UniqueCodeBundle:Reservation')->find($id);

            //validate reservation state before proceeding
            if ($reservation->getHotelConfirmationDate()
                || !$reservation->getHotelRefuseDate()
                || $reservation->getCustomerAcceptanceDate()
                || $reservation->getCustomerDeclineDate()) {

                return new JsonResponse(array('error' => 'Cette réservation n\'est pas dans un status adéquat'));
            }

            //alter reservation
            /**
             * @var $reservationService \Coffeeandbrackets\UniqueCodeBundle\Service\Reservation
             */
            $reservationService = $this->get('unique_code.reservation');
            $reservationService->customerAcceptHotelProposing($reservation);

            return new JsonResponse(array());
        }
        return new Response("Action not allowed", 400);
    }

    public function ajaxCustomerDeclineHotelProposingAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $id = $request->get('id');

            /**
             * @var Reservation $reservation
             */
            $reservation = $this->getDoctrine()->getRepository('UniqueCodeBundle:Reservation')->find($id);

            //validate reservation state before proceeding
            if ($reservation->getHotelConfirmationDate()
                || !$reservation->getHotelRefuseDate()
                || $reservation->getCustomerAcceptanceDate()
                || $reservation->getCustomerDeclineDate()) {

                return new JsonResponse(array('error' => 'Cette réservation n\'est pas dans un status adéquat'));
            }

            //alter reservation
            /**
             * @var $reservationService \Coffeeandbrackets\UniqueCodeBundle\Service\Reservation
             */
            $reservationService = $this->get('unique_code.reservation');
            $reservationService->customerDeclineHotelProposing($reservation);

            return new JsonResponse(array());
        }
        return new Response("Action not allowed", 400);
    }
}
