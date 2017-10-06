<?php

namespace Coffeeandbrackets\UniqueCodeBundle\Controller;

use Coffeeandbrackets\UniqueCodeBundle\Entity\Customer;
use Coffeeandbrackets\UniqueCodeBundle\Entity\Reservation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('UniqueCodeBundle:Default:index.html.twig');
    }

    /**
     * @param Request $request
     * @return JsonResponse|Response
     */
    public function submitCustomerInformationAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $code = $request->get('code');
            $checker = $this->get('unique_code.check_code');
            return new JsonResponse($checker->validate($code));
        }
        return new Response("Action not allowed", 400);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function submitReservationAction(Request $request){
        if ($request->isXmlHttpRequest()) {
            //TODO validation data

            $customer = new Customer();
            $customer->setFirstName($request->get('first_name'));
            $customer->setLastName($request->get('last_name'));
            $customer->setEmail($request->get('email'));
            $customer->setAcceptNewsletter(false);

            $reservation = new Reservation();
            $reservation->setCode($request->get('code'));
            $reservation->setAddDate(new \DateTime());
            $reservation->setUpdateDate(new \DateTime());
            $reservation->setReservationDate(new \DateTime());
            $reservation->setNumberNight($request->get('number_night'));
            $reservation->setNumberPerson($request->get('number_person'));
            $reservation->setHotel($request->get('hotel'));
            $reservation->setOffer($request->get('offer'));
            $reservation->setCustomerMsg($request->get('customer_msg'));
            $reservation->setCustomer($customer);

            $em = $this->getDoctrine()->getManager();
            $em->persist($reservation);

            $code = $em->getRepository('UniqueCodeBundle:Code')->findOneBy(['code' => $request->get('code')]);

            $workflow = $this->get('workflow.status_code');
            if($workflow->can($code, 'request')){
                $workflow->apply($code, 'request');
            }

            $em->flush();

            // TODO get hotel email from extern BD
            // Send mail to hotel
            $serviceMail = $this->container->get('unique_code.mailer');
            $tabParam = array(
                'to' => 'hotel@hotel.com',
                'template' => 'UniqueCodeBundle:Email:new_reservation_request.html.twig',
                'subject' => 'Demande de réservation',
                'from' => array($this->container->getParameter('mailer_user') => 'HappyBreak'),
                'params' => array(
                    'customer' => $customer,
                    'reservation' => $reservation
                )
            );

            // send mail
            $serviceMail->sendMessage($tabParam, 'text/html');

            return new JsonResponse('ok');
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
}
