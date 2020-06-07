<?php

namespace EasyApiBundle\Form\Type\User;

use EasyApiBundle\Entity\User\Mail;
use EasyApiBundle\Entity\User\User;
use EasyApiBundle\Form\Type\AbstractApiType;
use EasyApiBundle\Util\ApiProblem;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractApiType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'username',
                TextType::class,
                [
                    'constraints' => [
                        new Assert\Length([
                            'max' => 255,
                            'maxMessage' => sprintf(ApiProblem::ENTITY_FIELD_TOO_LONG, 'user', 'username'),
                        ]),
                        new Assert\NotBlank([
                            'groups' => 'creation',
                        ]),
                        new Assert\Regex([
                            'message' => ApiProblem::USER_USERNAME_ANONYMOUS_NOT_ALLOWED,
                            'pattern' => '/^'.User::ANONYMOUS_PREFIX.'/',
                            'match' => false,
                        ]),
                    ],
                ]
            )
            ->add(
                'email',
                TextType::class,
                [
                    'constraints' => [
                        new Assert\Length([
                            'max' => 255,
                            'maxMessage' => sprintf(ApiProblem::ENTITY_FIELD_TOO_LONG, 'user', 'email'),
                        ]),
                        new Assert\Regex([
                            'pattern' => Mail::MAIL_REGEX,
                            'message' => ApiProblem::USER_EMAIL_MALFORMED,
                        ]),
                        new Assert\NotBlank(),
                    ], ])
            ->add('plainPassword', PasswordType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => 255,
                        'maxMessage' => sprintf(ApiProblem::ENTITY_FIELD_TOO_LONG, 'user', 'plainPassword'),
                    ]),
                    new Assert\NotBlank([
                        'groups' => 'creation',
                    ]),
                ],
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => User::class,
            'cascade_validation' => true,
            'validation_groups' => function (FormInterface $form) {
                $user = $form->getData();

                if (!$user || null === $user->getId()) {
                    return ['Default', 'creation'];
                }

                return ['Default'];
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'user';
    }
}
