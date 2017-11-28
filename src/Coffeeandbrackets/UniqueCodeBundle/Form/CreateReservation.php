<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeBundle\Form;

use Coffeeandbrackets\UniqueCodeBundle\Service\CheckCode;
use Coffeeandbrackets\UniqueCodeBundle\Service\Hotels;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CreateReservation extends AbstractType
{

    private $hotels;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $codeCallback = function ($code, ExecutionContextInterface $context, $payload) use ($options) {
            /**
             * @var $checker CheckCode
             */
            $checker = $options['code_check'];

            switch ($checker->validate($code)) {
                case CheckCode::INVALID_CODE_NOT_FOUND:
                    $context->buildViolation('Le code unique indiqué n\'est pas valide.')
                            ->atPath('firstName')
                            ->addViolation();
                    break;
                case CheckCode::INVALID_CODE_USED:
                    $context->buildViolation('Le code unique indiqué a déjà été utilisé.')
                            ->atPath('firstName')
                            ->addViolation();
                    break;
                case CheckCode::INVALID_CODE_RESERVED:
                    $context->buildViolation('Le code unique indiqué a déjà une demande de reservation en cours. Vous ne pouvez pas envoyer plusieurs demandes de réservation en même temps.')
                            ->atPath('firstName')
                            ->addViolation();
                    break;
                default:
                    break;
            }
        };

        $hotelCallback = function ($hotelId, ExecutionContextInterface $context, $payload) use ($options) {
            /**
             * @var $hotelsService Hotels
             */
            $hotelsService = $options['hotels_service'];
            $allData       = $context->getRoot()->getData();
            $this->hotels        = $hotelsService->findAllByName($allData['hotel-name']);//validation of hotel-name must be earlier, this way we are sure we have a value
            if ( ! isset($this->hotels[$hotelId])) {
                $context->buildViolation('Hôtel invalide')
                        ->atPath('hotel')
                        ->addViolation();
            }
        };

        $offerCallback = function ($formulaId, ExecutionContextInterface $context, $payload) {
            $allData       = $context->getRoot()->getData();
            $hotelId = $allData['hotel'];
            //hotels must already set by last hotel check
            if ( ! isset($this->hotels[$hotelId]['formulas'][$formulaId])) {
                $context->buildViolation('Formule invalide')
                        ->atPath('hotel')
                        ->addViolation();
            }
        };

        $builder
            ->add('code', null, array('required' => true, 'constraints' => array(new Callback($codeCallback))))
            ->add('last_name', null, array('required' => true))
            ->add('first_name', null, array('required' => true))
            ->add('email', EmailType::class, array('required' => true))
            ->add('re_email', EmailType::class, array('required' => true))
            ->add('phone', null, array('required' => true))
            ->add('cgv', null, array('required' => true))
            ->add('number_person', null, array('required' => true))
            ->add('hotel-name', null, array('required' => true))
            ->add('hotel', null, array('required' => true, 'constraints' => array(new Callback($hotelCallback))))
            ->add('date', null, array('required' => true, 'constraints' => array(new DateTime(array('format' => 'd/m/Y')))))
            ->add('number_night', null, array('required' => true))
            ->add('offer', null, array('required' => true, 'constraints' => array(new Callback($offerCallback))))
            ->add('offer-name', null, array('required' => true))
            ->add('customer_msg', TextareaType::class, array('empty_data' => '', 'constraints' => array(new Length(array('max' => 255)))));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));

        $resolver->setRequired('hotels_service');
        $resolver->setRequired('code_check');
    }
}