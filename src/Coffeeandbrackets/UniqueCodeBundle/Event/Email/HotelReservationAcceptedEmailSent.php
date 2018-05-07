<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeBundle\Event\Email;

use Coffeeandbrackets\UniqueCodeBundle\Event\EmailEvent;

class HotelReservationAcceptedEmailSent extends EmailEvent
{
    const NAME = 'email.hotel.reservation.accepted.sent';
}