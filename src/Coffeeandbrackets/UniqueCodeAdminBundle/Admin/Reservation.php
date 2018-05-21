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
    protected $translationDomain = 'UniqueCodeAdminBundle';

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
                   ->add('codeObject.currentStatus')
                   ->add('hotel')
                   ->add('offer')
                   ->add('numberPerson')
                   ->add('numberNight')
                   ->add('reservationDate')
                   ->add('customerMsg')
                   ->add('offerServiceAfternoon')
                   ->add('offerServiceNight')
                   ->add('offerServiceMorning')
                   ->add('offerPrice')
                   ->add('hotelEmail')
                   ->add('hotelPhone')
                   ->add('hotelAddress')
                   ->add('campaign.name')
                   ->add('hotelConfirmationDate')
                   ->add('hotelRefuseDate')
                   ->add('hotelRefuseReason')
                   ->add('hotelProposedCheckInDate')
                   ->add('hotelProposedNumberNight')
                   ->add('customerAcceptanceDate')
                   ->add('customerDeclineDate')
                   ->add('isAutoCustomerDeclineDate')
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
        return [
            $this->trans('show.label_id') => 'id',
            $this->trans('show.label_code') => 'code',
            $this->trans('show.label_code_object_current_status') => 'codeObject.currentStatus',
            $this->trans('show.label_customer_gender') => 'customer.gender',
            $this->trans('show.label_customer_first_name') => 'customer.firstName',
            $this->trans('show.label_customer_last_name') => 'customer.lastName',
            $this->trans('show.label_customer_email') => 'customer.email',
            $this->trans('show.label_customer_phone') => 'customer.phone',
            $this->trans('show.label_campaign_name') => 'campaign.name',
            $this->trans('show.label_number_person') => 'numberPerson',
            $this->trans('show.label_hotel') => 'hotel',
            $this->trans('show.label_hotel_email') => 'hotelEmail',
            $this->trans('show.label_offer') => 'offer',
            $this->trans('show.label_reservation_date') => 'reservationDate',
            $this->trans('show.label_number_night') => 'numberNight',
            $this->trans('show.label_customer_msg') => 'customerMsg',
            $this->trans('show.label_add_date') => 'addDate',
            $this->trans('show.label_update_date') => 'updateDate',
            $this->trans('show.label_hotel_confirmation_date') => 'hotelConfirmationDate',
            $this->trans('show.label_hotel_refuse_date') => 'hotelRefuseDate',
            $this->trans('show.label_hotel_refuse_reason') => 'hotelRefuseReason',
            $this->trans('show.label_hotel_proposed_check_in_date') => 'hotelProposedCheckInDate',
            $this->trans('show.label_hotel_proposed_number_night') => 'hotelProposedNumberNight',
            $this->trans('show.label_customer_acceptance_date') => 'customerAcceptanceDate',
            $this->trans('show.label_customer_decline_date') => 'customerDeclineDate',
            $this->trans('show.label_is_auto_customer_decline_date') => 'isAutoCustomerDeclineDate',
            $this->trans('show.label_offer_service_afternoon') => 'offerServiceAfternoon',
            $this->trans('show.label_offer_service_night') => 'offerServiceNight',
            $this->trans('show.label_offer_service_morning') => 'offerServiceMorning',
            $this->trans('show.label_offer_price') => 'offerPrice',
            $this->trans('show.label_hotel_phone') => 'hotelPhone',
            $this->trans('show.label_hotel_address') => 'hotelAddress'
        ];
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('list', 'export', 'show'));
    }

    public function toString($object)
    {
        return $object instanceof \Coffeeandbrackets\UniqueCodeBundle\Entity\Reservation
            ? 'Réservation #' . $object->getId()
            : 'Reservation'; // shown in the breadcrumb on the create view
    }
}