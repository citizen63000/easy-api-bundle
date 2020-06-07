<?php

namespace EasyApiBundle\Form\Type\User;

use EasyApiBundle\Form\Model\User\ResetPassword;
use EasyApiBundle\Form\Type\AbstractApiType;
use EasyApiBundle\Util\ApiProblem;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ResetPasswordType extends AbstractApiType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, ['constraints' => [
                new Assert\Length([
                    'max' => 255,
                    'maxMessage' => sprintf(ApiProblem::ENTITY_FIELD_TOO_LONG, 'user', 'username'),
                ]),
                new Assert\NotBlank(),
            ]])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('data_class', ResetPassword::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'resetPassword';
    }
}
