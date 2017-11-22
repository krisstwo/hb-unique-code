<?php

namespace Coffeeandbrackets\UniqueCodeBundle\Controller;

use Coffeeandbrackets\UniqueCodeBundle\Entity\Customer;
use Coffeeandbrackets\UniqueCodeBundle\Entity\Reservation;
use Coffeeandbrackets\UniqueCodeBundle\Event\Reservation\ReservationCreated;
use Coffeeandbrackets\UniqueCodeBundle\Form\CreateCustomer;
use Coffeeandbrackets\UniqueCodeBundle\Form\CreateReservation;
use Coffeeandbrackets\UniqueCodeBundle\Form\HotelRefuseReservation;
use Coffeeandbrackets\UniqueCodeBundle\Service\Campaign;
use Coffeeandbrackets\UniqueCodeBundle\Service\CheckCode;
use Coffeeandbrackets\UniqueCodeBundle\Service\Hotels;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcherInterface;
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

            /**
             * @var $checker CheckCode
             */
            $checker = $this->get('unique_code.check_code');

            $form = $this->get('form.factory')->createNamedBuilder('', CreateCustomer::class, array(), array('code_check' => $checker, 'allow_extra_fields' => true))->getForm();
            $form->handleRequest($request);

            if ( ! $form->isSubmitted() || ! $form->isValid()) {
                return new JsonResponse(array('error' => 'Données invalides'));
            }

            return new JsonResponse(array());
        }

        return new Response("Action not allowed", 400);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function ajaxValidateCodeAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {

            /**
             * @var $checker CheckCode
             */
            $checker = $this->get('unique_code.check_code');

            $code = $request->get('code');

            switch ($checker->validate($code)) {
                case CheckCode::INVALID_CODE_NOT_FOUND:
                    return new JsonResponse('Le code unique indiqué n\'est pas valide.');
                    break;
                case CheckCode::INVALID_CODE_USED:
                    return new JsonResponse('Le code unique indiqué a déjà été utilisé.');
                    break;
                case CheckCode::INVALID_CODE_RESERVED:
                    return new JsonResponse('Le code unique indiqué a déjà une demande de reservation en cours. Vous ne pouvez envoyer plusieurs demandes de réservation en même temps.');
                    break;
                default:
                    /**
                     * @var $reservationService \Coffeeandbrackets\UniqueCodeBundle\Service\Reservation
                     */
                    $reservationService = $this->get('unique_code.reservation');
                    $reservationService->activateCode($code);

                    return new JsonResponse(true);
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

            /**
             * @var $codeChecker CheckCode
             */
            $codeChecker = $this->get('unique_code.check_code');

            /**
             * @var $dispatcher TraceableEventDispatcherInterface
             */
            $dispatcher = $this->get('event_dispatcher');

            //Validation
            /**
             * @var Form $form
             */
            $form = $this->get('form.factory')->createNamedBuilder('', CreateReservation::class, array(), array('allow_extra_fields' => true, 'hotels_service' => $hotelsService, 'code_check' => $codeChecker))->getForm();
            $form->handleRequest($request);

            if ( ! $form->isSubmitted() || ! $form->isValid()) {
                return new JsonResponse(array('error' => 'Données invalides', 'details' => (string) $form->getErrors(true)));
            }

            $data = $form->getData();

            $customer = new Customer();
            $customer->setFirstName($data['first_name']);
            $customer->setLastName($data['last_name']);
            $customer->setEmail($data['email']);
            $customer->setAcceptNewsletter(isset($data['newsletter']) ? true : false);
            $customer->setCampaign($campaign);

            $reservation = new Reservation();
            $reservation->setCode($data['code']);
            $reservation->setReservationDate(date_create_from_format('d/m/Y', $data['date']));
            $reservation->setNumberNight($data['number_night']);
            $reservation->setNumberPerson($data['number_person']);

            //Hotel validated in form, can use label directly
            $reservation->setHotel($data['hotel-name']);

            $reservation->setOffer($data['offer-name']);

            $reservation->setCustomerMsg($data['customer_msg']);
            $reservation->setCustomer($customer);
            $reservation->setCampaign($campaign);

            $em = $this->getDoctrine()->getManager();
            $em->persist($reservation);
            $em->flush();

            $event = new ReservationCreated($reservation);
            $dispatcher->dispatch(ReservationCreated::NAME, $event);

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

        /**
         * @var $reservationService \Coffeeandbrackets\UniqueCodeBundle\Service\Reservation
         */
        $reservationService = $this->get('unique_code.reservation');
        $reservationService->hotelAcceptReservation($reservation);

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
                $reservationService->hotelRefuseReservation($reservation, $data);

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
