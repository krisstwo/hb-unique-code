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

class Reservation extends AbstractAdmin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('_action', null, array(
                'actions' => array(
                    'show' => array()
                )
            ))
            ->add('id')
            ->add('code')
            ->add('hotel')
            ->add('reservationDate')
            ->add('addDate')
            ->add('updateDate')
            ->add('campaign.name');
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('code')
                       ->add('hotel')
                       ->add('reservationDate')
                       ->add('addDate')
                       ->add('campaign', null, array(), 'entity', array(
                           'class'        => 'Coffeeandbrackets\UniqueCodeBundle\Entity\Campaign',
                           'choice_label' => 'name',
                       ));
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper->add('id')
                   ->add('code')
                   ->add('hotel')
                   ->add('offer')
                   ->add('numberPerson')
                   ->add('numberNight')
                   ->add('reservationDate')
                   ->add('customerMsg')
                   ->add('campaign.name')
                   ->add('hotelConfirmationDate')
                   ->add('hotelRefuseDate')
                   ->add('hotelRefuseReason')
                   ->add('hotelProposedCheckInDate')
                   ->add('hotelProposedCheckOutDate')
                   ->add('customerAcceptanceDate')
                   ->add('customerDeclineDate')
                   ->add('customer.firstName')
                   ->add('customer.lastName')
                   ->add('addDate')
                   ->add('updateDate');
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('list', 'export', 'show'));
    }
}