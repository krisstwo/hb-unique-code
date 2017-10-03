<?php

namespace Coffeeandbrackets\UniqueCodeBundle\Controller;

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
    public function checkCodeAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $code = $request->get('code');
            $checker = $this->get('unique_code.check_code');
            return new JsonResponse($checker->validate($code));
        }
        return new Response("Action not allowed", 400);
    }
}
