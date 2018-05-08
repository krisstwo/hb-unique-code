<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeAdminBundle\Controller;

use Coffeeandbrackets\UniqueCodeBundle\Entity\EmailLog;
use Coffeeandbrackets\UniqueCodeBundle\Event\Email\ResendEmailSent;
use Coffeeandbrackets\UniqueCodeBundle\Event\EmailEvent;
use Coffeeandbrackets\UniqueCodeBundle\Service\Mailer;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Coffeeandbrackets\UniqueCodeAdminBundle\Form\ForwardEmailLog;

class EmailLogController extends Controller
{
    /**
     * @param $id
     */
    public function resendAction($id)
    {
        /**
         * @var EmailLog $emailLogEntry
         */
        $emailLogEntry = $this->admin->getSubject();

        if ( ! $emailLogEntry) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id: %s', $id));
        }

        /**
         * @var Mailer $mailer
         * @var EventDispatcherInterface $eventDispatcher
         */
        $mailer          = $this->container->get('unique_code.mailer'); //TODO: Inject service into controller
        $eventDispatcher = $this->container->get('event_dispatcher');

        $recipient   = $emailLogEntry->getTo();
        $subject     = $emailLogEntry->getSubject();
        $reservation = $emailLogEntry->getReservation();
        $mailConfig  = array(
            'to'      => $recipient,
            'subject' => $subject,
            'from'    => $emailLogEntry->getFrom(),
            'bcc'     => $emailLogEntry->getBcc(),
            'body'    => $emailLogEntry->getBody()
        );
        $mailer->sendMessage($mailConfig, 'text/html');

        $this->addFlash('sonata_flash_success',
            sprintf('Email #%s resent successfully to %s', $emailLogEntry->getId(), $emailLogEntry->getTo()));

        $event = new ResendEmailSent($mailConfig['from'], $recipient, $mailConfig['bcc'], $subject, $mailer->getBody(),
            $reservation);
        $eventDispatcher->dispatch(EmailEvent::NAME, $event);

        return new RedirectResponse($this->admin->generateUrl('list',
            ['filter' => $this->admin->getFilterParameters()]));
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function forwardAction($id) {
        /**
         * @var EmailLog $emailLogEntry
         */
        $emailLogEntry = $this->admin->getSubject();

        if ( ! $emailLogEntry) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id: %s', $id));
        }

        $request = $this->getRequest();
        $form = $this->get('form.factory')->createNamedBuilder('', ForwardEmailLog::class)->getForm();
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            /**
             * @var Mailer $mailer
             * @var EventDispatcherInterface $eventDispatcher
             */
            $mailer          = $this->container->get('unique_code.mailer'); //TODO: Inject service into controller
            $eventDispatcher = $this->container->get('event_dispatcher');

            $recipient   = $request->get('email');
            $subject     = $emailLogEntry->getSubject();
            $reservation = $emailLogEntry->getReservation();
            $mailConfig  = array(
                'to'      => $recipient,
                'subject' => $subject,
                'from'    => $emailLogEntry->getFrom(),
                'bcc'     => $emailLogEntry->getBcc(),
                'body'    => $emailLogEntry->getBody()
            );
            $mailer->sendMessage($mailConfig, 'text/html');

            $this->addFlash('sonata_flash_success',
                sprintf('Email #%s forward successfully to %s', $emailLogEntry->getId(), $recipient));

            $event = new ResendEmailSent($mailConfig['from'], $recipient, $mailConfig['bcc'], $subject, $mailer->getBody(),
                $reservation);
            $eventDispatcher->dispatch(EmailEvent::NAME, $event);

            return new RedirectResponse($this->admin->generateUrl('list',
                ['filter' => $this->admin->getFilterParameters()]));
        }

        return $this->render('UniqueCodeAdminBundle:EmailLog:forward_form.html.twig', array(
            'object' => $emailLogEntry,
            'elements' => $this->admin->getShow(),
            'action' => $this->admin->generateObjectUrl('forward', $emailLogEntry),
            'form' => $form->createView()
        ));
    }
}