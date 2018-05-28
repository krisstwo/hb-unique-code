<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class HotelRefuseReservation extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $nightsCallback = function ($nights, ExecutionContextInterface $context, $payload) {
            $allData = $context->getRoot()->getData();
            if ( ! empty($allData['check-in-date']) && (empty($nights) || intval($nights) == 0)) {
                $context->buildViolation('Nuitées obligatoires')
                        ->atPath('nights')
                        ->addViolation();
            }

        };

        $checkInDateCallback = function ($checkInDate, ExecutionContextInterface $context, $payload) {

            if ( ! empty($allData['check-in-date']) && ! date_create_from_format('d/m/Y', $checkInDate)) {
                $context->buildViolation('Format date invalide')
                        ->atPath('check-in-date')
                        ->addViolation();
            }

        };

        $builder
            ->add('reason', null, array('required' => true, 'constraints' => array(new Length(array('min' => 1)))))
            ->add('check-in-date', null, array('constraints' => array(new Callback($checkInDateCallback))))
            ->add('nights', null, array(
                'constraints' => array(new Callback($nightsCallback))
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }
}