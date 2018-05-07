<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeAdminBundle\Admin;

use \Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class EmailLog extends AbstractAdmin
{
    protected $datagridValues = [
        '_sort_order' => 'DESC',
        '_sort_by'    => 'id',
    ];


    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('_action', null, array(
                'actions' => array(
                    'show' => array()
                )
            ))
            ->addIdentifier('id')
            ->add('reservation.hotel')
            ->add('event')
            ->add('eventDate')
            ->add('to')
            ->add('subject')
            ->add('reservation', null, array(
                'associated_property' => function (\Coffeeandbrackets\UniqueCodeBundle\Entity\Reservation $reservation
                ) {

                    return sprintf('#%s %s %s %s, %s', $reservation->getId(), $reservation->getCustomer()->getGender(),
                        $reservation->getCustomer()->getFirstName(), $reservation->getCustomer()->getLastName(),
                        $reservation->getAddDate()->format('Y-m-d H:i'));
                },
                'route'               => array('name' => 'show')
            ));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('reservation.hotel')
            ->add('event')//TODO : enum
            ->add('eventDate', 'doctrine_orm_datetime')
            ->add('from')
            ->add('to')
            ->add('bcc')
            ->add('subject')
            ->add('reservation.id');
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('reservation.hotel')
            ->add('event')
            ->add('eventName')
            ->add('from')
            ->add('to')
            ->add('bcc')
            ->add('subject')
            ->add('body', 'html', array('strip' => true))
            ->add('reservation', null, array(
                'associated_property' => function (\Coffeeandbrackets\UniqueCodeBundle\Entity\Reservation $reservation
                ) {

                    return sprintf('#%s %s %s %s, %s', $reservation->getId(), $reservation->getCustomer()->getGender(),
                        $reservation->getCustomer()->getFirstName(), $reservation->getCustomer()->getLastName(),
                        $reservation->getAddDate()->format('Y-m-d H:i'));
                },
                'route'               => array('name' => 'show')
            ));
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('list', 'export', 'show'));
    }

    public function toString($object)
    {
        return $object instanceof \Coffeeandbrackets\UniqueCodeBundle\Entity\EmailLog
            ? 'Email #' . $object->getId()
            : 'Email'; // shown in the breadcrumb on the create view
    }
}