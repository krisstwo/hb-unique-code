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
            $reservation->setCustomerMsg($request->get('customer_msg'));
            $reservation->setCustomer($customer);

            $em = $this->getDoctrine()->getManager();
            $em->persist($reservation);

            $code = $em->getRepository('UniqueCodeBundle:Code')->findOneBy(['code' => $request->get('code')]);
            $code->setCurrentStatus('waiting');

            $em->flush();

            return new JsonResponse('ok');
        }
        return new Response("Action not allowed", 400);
    }
}
