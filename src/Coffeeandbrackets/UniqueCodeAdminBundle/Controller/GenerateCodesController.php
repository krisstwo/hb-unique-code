<?php
/**
 * Coffee & Brackets software studio
 * @author Jebali Mohamed hedi <jebali.med.hedi@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeAdminBundle\Controller;

use Coffeeandbrackets\UniqueCodeAdminBundle\Form\GenerateCodes;
use Doctrine\ORM\Query\Expr\From;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GenerateCodesController extends Controller {

    public function generateAction() {

        // TODO generation code

        //$this->addFlash('sonata_flash_success', 'Generate successfully');

        //return new RedirectResponse($this->admin->generateUrl('list'));
        $form = $this->get('form.factory')->createNamedBuilder('', GenerateCodes::class)->getForm();
        return $this->render('UniqueCodeAdminBundle:Default:generation_codes_form.html.twig', array(
            'form' => $form->createView()
        ));
    }
}