<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeBundle\Service;


use Coffeeandbrackets\UniqueCodeBundle\Entity\ForfaitPlanning;
use Doctrine\ORM\EntityManager;

class Hotels
{

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Hotels constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function findOneByNameId($query, $id)
    {
        $hotels = $this->findAllByName($query);

        return isset($hotels[$id]) ? $hotels[$id] : null;
    }

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
                    'id'           => $hotelId,
                    'label'        => $item->titre,
                    'stars'        => $item->etoiles,
                    'informations' => trim($item->infos_pratiques),
                    'email'        => trim($item->mail_hotel),
                    'formulas'     => array()
                );
            }

            if ( ! isset($hotels[$hotelId]['formulas'][$item->nidforfait])) {
                //get forfait planing for this forfait
                $planinngGrid = $this->em->getRepository('UniqueCodeBundle:ForfaitPlanning')->findPlaningById($item->nidforfait);

                $forfaitPlanning = array();
                foreach ($planinngGrid as $planningLine) {
                    /**
                     * @var $planningLine ForfaitPlanning
                     */
                    $forfaitPlanning[] = array(
                        'year'  => $planningLine->getYear(),
                        'month' => $planningLine->getMonth(),
                        'days'  => $planningLine->getDaysArray()
                    );
                }

                //setup the structure
                $hotels[$hotelId]['formulas'][$item->nidforfait] = array(
                    'id'      => $item->nidforfait,
                    'label'   => $item->forfait,
                    'persons' => $this->extractPersons($item),
                    'nights'  => $this->extractNights($item),
                    'planning' => $forfaitPlanning
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