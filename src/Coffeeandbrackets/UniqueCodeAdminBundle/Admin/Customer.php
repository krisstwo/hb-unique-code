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

class Customer extends AbstractAdmin
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
            ->add('firstName')
            ->add('lastName')
            ->add('email')
            ->add('phone')
            ->add('campaign.name');
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('firstName')
                       ->add('lastName')
                       ->add('email')
                       ->add('phone')
                       ->add('campaign', null, array(), 'entity', array(
                           'class'        => 'Coffeeandbrackets\UniqueCodeBundle\Entity\Campaign',
                           'choice_label' => 'name',
                       ));;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper->add('id')
                   ->add('gender')
                   ->add('firstName')
                   ->add('lastName')
                   ->add('email')
                   ->add('phone')
                   ->add('acceptNewsletter')
                   ->add('campaign.name');
    }

    public function getExportFields()
    {
        return [
            $this->trans('show.label_id') => 'id',
            $this->trans('show.label_gender') => 'gender',
            $this->trans('show.label_first_name') => 'firstName',
            $this->trans('show.label_last_name') => 'lastName',
            $this->trans('show.label_email') => 'email',
            $this->trans('show.label_phone') => 'phone',
            $this->trans('show.label_accept_newsletter') => 'acceptNewsletter',
            $this->trans('show.label_campaign_name') => 'campaign.name'
        ];
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('list', 'export', 'show'));
    }

    public function toString($object)
    {
        return $object instanceof \Coffeeandbrackets\UniqueCodeBundle\Entity\Customer
            ? 'Client #' . $object->getId()
            : 'Client'; // shown in the breadcrumb on the create view
    }
}