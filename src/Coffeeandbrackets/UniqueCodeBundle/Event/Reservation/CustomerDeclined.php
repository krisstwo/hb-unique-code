<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeBundle\Event\Reservation;

use \Coffeeandbrackets\UniqueCodeBundle\Event\ReservationEvent;

class CustomerDeclined extends ReservationEvent
{
    const NAME = 'reservation.customer.declined';
}