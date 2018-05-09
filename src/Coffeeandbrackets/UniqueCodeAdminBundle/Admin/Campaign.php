<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeAdminBundle\Admin;

use \Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class Campaign extends AbstractAdmin
{
    protected $translationDomain = 'UniqueCodeAdminBundle';

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('_action', null, array(
                'actions' => array(
                    'show'   => array(),
                    'edit'   => array(),
                    'delete' => array()
                )
            ))
            ->addIdentifier('id')
            ->add('name', null, ['label' => 'campaign_name'])
            ->add('code')
            ->add('logo')
            ->add('creationDate')
            ->add('updateDate');
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('name')
                       ->add('code')
                       ->add('creationDate')
                       ->add('updateDate');
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper->add('name', 'text', ['label' => 'campaign_name'])
                   ->add('code', 'text')
                   ->add('logo', 'text');
    }

    public function configureShowFields(ShowMapper $showMapper)
    {
        parent::configureShowFields($showMapper);

        $showMapper->add('id')
                   ->add('name', null, ['label' => 'campaign_name'])
//                   ->add('code')
                   ->add('logo')
                   ->add('creationDate')
                   ->add('updateDate')
                   ->add('code', 'url', array(
                       'attributes' => array('target' => '_blank'),
                       'route' => array(
                           'name' => 'unique_code_campaign',
                           'absolute' => true,
                           'identifier_parameter_name' => 'campaignCode',
                           'identifier_parameter_value' => true // Use the field value for this url parameter
                       )
                   ));
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('list', 'export', 'create', 'show', 'edit', 'delete'));
    }

    public function toString($object)
    {
        return $object instanceof \Coffeeandbrackets\UniqueCodeBundle\Entity\Campaign
            ? 'Campaign #' . $object->getId()
            : 'Campaign'; // shown in the breadcrumb on the create view
    }
}