<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Length;

class CreateReservation extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', null, array('required' => true))
            ->add('last_name', null, array('required' => true))
            ->add('first_name', null, array('required' => true))
            ->add('email', EmailType::class, array('required' => true))
            ->add('re_email', EmailType::class, array('required' => true))
            ->add('phone', null, array('required' => true))
            ->add('cgv', null, array('required' => true))
            ->add('number_person', null, array('required' => true))
            ->add('hotel', null, array('required' => true))
            ->add('hotel-name', null, array('required' => true))
            ->add('date', null, array('required' => true, 'constraints' => array(new DateTime(array('format' => 'd/m/Y')))))
            ->add('number_night', null, array('required' => true))
            ->add('offer', null, array('required' => true))
            ->add('customer_msg', TextareaType::class, array('empty_data' => '', 'constraints' => array(new Length(array('max' => 255)))));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }
}