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
            ->add('codeObject.currentStatus')
            ->add('hotel')
            ->add('addDate')
            ->add('updateDate')
            ->add('customer.firstName')
            ->add('customer.lastName')
            ->add('customer.email')
            ->add('customer.phone')
            ->add('campaign.name');
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('code')
                       ->add('hotel')
                       ->add('reservationDate')
                       ->add('customer.firstName')
                       ->add('customer.lastName')
                       ->add('customer.email')
                       ->add('customer.phone')
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
                   ->add('hotelProposedNumberNight')
                   ->add('customerAcceptanceDate')
                   ->add('customerDeclineDate')
                   ->add('customer.gender')
                   ->add('customer.firstName')
                   ->add('customer.lastName')
                   ->add('customer.email')
                   ->add('customer.phone')
                   ->add('addDate')
                   ->add('updateDate');
    }

    public function getExportFields()
    {
        return ['id','code','codeObject.currentStatus','customer.gender','customer.firstName','customer.lastName','customer.email','customer.phone','campaign.name','numberPerson','hotel','hotelEmail','offer','reservationDate','numberNight','customerMsg','addDate','updateDate','hotelConfirmationDate','hotelRefuseDate','hotelRefuseReason','hotelProposedCheckInDate','hotelProposedNumberNight','customerAcceptanceDate','customerDeclineDate','isAutoCustomerDeclineDate','offerServiceAfternoon','offerServiceNight','offerServiceMorning','offerPrice','hotelPhone','hotelAddress'];
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('list', 'export', 'show'));
    }
}