<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeBundle\Event\Reservation;

use \Coffeeandbrackets\UniqueCodeBundle\Event\ReservationEvent;

class ReservationCreated extends ReservationEvent
{
    const NAME = 'reservation.created';
}