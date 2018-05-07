<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeBundle\Event\Email;

use Coffeeandbrackets\UniqueCodeBundle\Event\EmailEvent;

class CustomerReservationDeclinedEmailSent extends EmailEvent
{
    const NAME = 'email.customer.reservation.declined.sent';
}