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
        $unwanted_array = array(
            'Š' => 'S',
            'š' => 's',
            'Ž' => 'Z',
            'ž' => 'z',
            'À' => 'A',
            'Á' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Ä' => 'A',
            'Å' => 'A',
            'Æ' => 'A',
            'Ç' => 'C',
            'È' => 'E',
            'É' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Ì' => 'I',
            'Í' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ñ' => 'N',
            'Ò' => 'O',
            'Ó' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ø' => 'O',
            'Ù' => 'U',
            'Ú' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ý' => 'Y',
            'Þ' => 'B',
            'ß' => 'Ss',
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'æ' => 'a',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ð' => 'o',
            'ñ' => 'n',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ø' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ý' => 'y',
            'þ' => 'b',
            'ÿ' => 'y'
        );
        $query          = strtr($query, $unwanted_array);

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
                    'phone'        => trim($item->telephone_hotel),
                    'address'      => trim($item->rue_hotel).', '.trim($item->postal_code).' '.trim($item->ville_hotel).', '.trim($item->pays_hotel),
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
                        'days'  => $planningLine->getDaysArray(),
                        'service_afternoon' => $planningLine->getServiceAfternoon(),
                        'service_night' => $planningLine->getServiceNight(),
                        'service_morning' => $planningLine->getServiceMorning()
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
                $possibleValues = array(1, 2);
                break;
            case 'Cette formule est réservable un maximum de 3 nuitées consécutives':
                $possibleValues = array(1, 2, 3);
                break;
            case 'Cette formule est réservable plusieurs nuitées consécutives' :
                $possibleValues = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
                break;
            default:
                break;
        }

        return $possibleValues;
    }
}