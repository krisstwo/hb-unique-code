<?php
/**
 * Coffee & Brackets software studio
 * @author Jebali Mohamed hedi <jebali.med.hedi@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeAdminBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class GenerateCodes extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
            ->add('campaign', null, array('required' => true))
            ->add('prefix', null, array('required' => true))
            ->add('quantity', null, array('required' => true));
    }
}