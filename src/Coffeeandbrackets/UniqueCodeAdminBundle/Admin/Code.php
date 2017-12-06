<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeAdminBundle\Admin;

use \Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Knp\Menu\ItemInterface as MenuItemInterface;

class Code extends AbstractAdmin
{
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('_action', null, array(
                'actions' => array(
                    'edit' => array()
                )
            ))
            ->add('id')
            ->add('code')
            ->add('currentStatus')
            ->add('campaign.name');
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('code')
                       ->add('currentStatus')
                       ->add('campaign', null, array(), 'entity', array(
                           'class'        => 'Coffeeandbrackets\UniqueCodeBundle\Entity\Campaign',
                           'choice_label' => 'name',
                       ));;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper->add('code', 'text', array('disabled' => true))
                   ->add('currentStatus', 'choice', array(
                       'choices' => array(
                           'not_actived' => 'not_actived',
                           'actived'     => 'actived',
                           'waiting'     => 'waiting',
                           'used'        => 'used'
                       )
                   ))
                   ->add('campaign', 'entity', array(
                       'class'        => 'Coffeeandbrackets\UniqueCodeBundle\Entity\Campaign',
                       'choice_label' => 'name',
                   ));
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('list', 'export', 'edit'));
        $collection->add('generate', 'generate');
    }

    public function configureActionButtons($action, $object = null) {
        $list = parent::configureActionButtons($action, $object);
        $this->setTemplate('button_generate', 'list__action_generate.html.twig');
        $list['generate'] = array(
            'template' => $this->getTemplate('button_generate'),
        );

        return $list;
    }

    public function toString($object)
    {
        return $object instanceof \Coffeeandbrackets\UniqueCodeBundle\Entity\Code
            ? 'Code ' . $object->getCode()
            : 'Code'; // shown in the breadcrumb on the create view
    }
}