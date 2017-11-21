<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeBundle\Event\Reservation;

use \Coffeeandbrackets\UniqueCodeBundle\Event\ReservationEvent;

class CodeActivated extends ReservationEvent
{
    const NAME = 'reservation.code.activated';
}