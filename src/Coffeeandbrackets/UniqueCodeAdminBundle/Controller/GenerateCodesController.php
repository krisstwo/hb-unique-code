<?php
/**
 * Coffee & Brackets software studio
 * @author Jebali Mohamed hedi <jebali.med.hedi@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeAdminBundle\Controller;

use Coffeeandbrackets\UniqueCodeAdminBundle\Form\GenerateCodes;
use Coffeeandbrackets\UniqueCodeBundle\Entity\Code;
use Hashids\Hashids;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GenerateCodesController extends Controller {

    public function generateAction() {

        $request = $this->getRequest();
        $form = $this->get('form.factory')->createNamedBuilder('', GenerateCodes::class)->getForm();
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $statingNumber = 1;
            $suffix = $request->get('prefix');
            $howMany = $request->get('quantity');

            $hashids = new Hashids('happybreak' . $suffix, 4, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');

            $campaign = $this->getDoctrine()->getRepository('UniqueCodeBundle:Campaign')->find($request->get('campaign'));
            for ($i = 0; $i < $howMany; $i++) {
                $code = $suffix . $hashids->encode($statingNumber + $i);

                $codeEntity = new Code();
                $codeEntity->setCode($code);
                $codeEntity->setCampaign($campaign);
                $codeEntity->setCurrentStatus('not_actived');
                $codeEntity->setClear('');

                $this->getDoctrine()->getManager()->persist($codeEntity);
                $this->getDoctrine()->getManager()->flush();
            }

            $this->addFlash('sonata_flash_success', 'Generate successfully');
            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        return $this->render('UniqueCodeAdminBundle:Default:generation_codes_form.html.twig', array(
            'action' => '',
            'form' => $form->createView()
        ));
    }
}