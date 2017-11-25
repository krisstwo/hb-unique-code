<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeBundle\Service;


use Coffeeandbrackets\UniqueCodeBundle\Entity\Reservation;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RequestStack;

class Hotels
{
    public function findAllByName($query)
    {
        //TODO: more solid fix
        $query = str_replace(str_split('ô'), str_split('o'), strtolower($query));
        $query = trim(iconv("UTF-8", "ASCII//TRANSLIT", $query));

        //fetch data
        $response = \Unirest\Request::get('https://happybreak.com/api/operation/hotels', array(),
            array('hotel' => $query));//TODO: move to config


        //results are a formulas array, must group them by hotel
        $hotels = array();
        foreach ($response->body as $item) {
            $hotelId = preg_replace('#\/hotel\/(\d+)#', '${1}', $item->path);
            if (empty($hotelId)) {
                continue;
            }

            if ( ! isset($hotels[$hotelId])) {
                $hotels[$hotelId] = array(
                    'id'       => $hotelId,
                    'label'     => $item->titre,
                    'stars'    => $item->etoiles,
                    'informations'    => trim($item->infos_pratiques),
                    'formulas' => array()
                );
            }

            if ( ! isset($hotels[$hotelId]['formulas'][$item->nidforfait])) {
                $hotels[$hotelId]['formulas'][$item->nidforfait] = array(
                    'id'      => $item->nidforfait,
                    'label'   => $item->forfait,
                    'persons' => $this->extractPersons($item),
                    'nights'  => $this->extractNights($item),
                );
            }
        }

        return $hotels;
    }

    private function extractPersons($formula)
    {
        $possibleValues = array();

        switch ($formula->formules_disponibles) {
            case '2':
                $possibleValues[] = 2;
                break;
            case '1':
                $possibleValues[] = 1;
                break;
            default:
                $possibleValues[] = 1;
                $possibleValues[] = 2;
                break;
        }

        return $possibleValues;
    }

    private function extractNights($formula)
    {
        $possibleValues = array();

        switch ($formula->nuitee) {
            case 'Cette formule est réservable un maximum de 1 nuitée consécutive':
                $possibleValues[] = 1;
                break;
            case 'Cette formule est réservable un maximum de 2 nuitées consécutives':
                $possibleValues[] = 2;
                break;
            default:
                $possibleValues[] = 1;
                $possibleValues[] = 2;
                $possibleValues[] = 3;
                break;
        }

        return $possibleValues;
    }
}