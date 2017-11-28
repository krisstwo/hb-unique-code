<?php
/**
 * Coffee & Brackets software studio
 * @author Mohamed KRISTOU <krisstwo@gmail.com>.
 */

namespace Coffeeandbrackets\UniqueCodeBundle\Form;

use Coffeeandbrackets\UniqueCodeBundle\Service\CheckCode;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CreateCustomer extends AbstractType
{
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

        $builder
            ->add('code', null, array('required' => true, 'constraints' => array(new Callback($codeCallback))))
            ->add('gender', null, array('required' => true))
            ->add('last_name', null, array('required' => true))
            ->add('first_name', null, array('required' => true))
            ->add('email', EmailType::class, array('required' => true))
            ->add('re_email', EmailType::class, array('required' => true))
            ->add('phone', null, array('required' => true, 'constraints' => array(new Regex(['pattern' => "/^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/"]))))
            ->add('cgv', null, array('required' => true));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));

        $resolver->setRequired('code_check');
    }
}