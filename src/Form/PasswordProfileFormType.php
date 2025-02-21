<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PasswordProfileFormType extends AbstractType
{
    private $security;
    private $userPasswordHasher;
    private $translator;

    public function __construct(Security $security, UserPasswordHasherInterface $userPasswordHasher, TranslatorInterface $translator)
    {
        $this->security = $security;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'Current Password',
                'mapped' => false
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'first_options'  => [
                    'label' => 'New Password'
                ],
                'second_options' => [
                    'label' => 'Repeat Password'
                ],
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
            ])
        ;

        // Check Current-password
        $currentPasswordValidator = function(FormEvent $event) {
            $user = $this->security->getUser();
            $form = $event->getForm();

            $currentPass = $form->get('currentPassword')->getData();

            if (!$this->userPasswordHasher->isPasswordValid($user, $currentPass)) {
                $form['currentPassword']->addError(
                    new FormError(
                        $this->translator->trans('Invalid current password')
                    )
                );
            }
        };
        $builder->addEventListener(FormEvents::POST_SUBMIT, $currentPasswordValidator);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
