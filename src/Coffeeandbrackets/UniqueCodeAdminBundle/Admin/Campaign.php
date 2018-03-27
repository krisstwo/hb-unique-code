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

class Campaign extends AbstractAdmin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('_action', null, array(
                'actions' => array(
                    'edit'   => array(),
                    'delete' => array()
                )
            ))
            ->add('id')
            ->add('name')
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
        $formMapper->add('name', 'text')
                   ->add('code', 'text')
                   ->add('logo', 'text');
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('list', 'export', 'create', 'edit', 'delete'));
    }

    public function toString($object)
    {
        return $object instanceof \Coffeeandbrackets\UniqueCodeBundle\Entity\Campaign
            ? 'Campaign #' . $object->getId()
            : 'Campaign'; // shown in the breadcrumb on the create view
    }
}