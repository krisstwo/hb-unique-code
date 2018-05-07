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
}