<?php
/**
 * Coffee & Brackets software studio
 * @author Jebali Mohamed hedi <jebali.med.hedi@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeAdminBundle\Form;


use Coffeeandbrackets\UniqueCodeBundle\Entity\Campaign;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenerateCodes extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
            ->add('campaign', EntityType::class, array(
                'class' => Campaign::class,
                'choice_label' => 'name',
                'required' => false
            ))
            ->add('prefix', null, array('required' => true))
            ->add('quantity', null, array('required' => true));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'allow_extra_fields' => true
            )
        );
    }
}