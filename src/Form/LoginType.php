<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, array(
                'label' => false,
                'attr' => array(
                    'placeholder' => 'Pseudo'
                )
            ))
            ->add('password', PasswordType::class, array(
                'label' => false,
                'attr' => array(
                    'placeholder' => 'Mot de passe'
                )
            ))
            ->add('login', SubmitType::class, array(
                'attr' => array(
                    'class' => 'btn btn-primary'
                ),
                'label' => 'Se connecter'
            ))
            ->add('remember_me', CheckboxType::class, array(
                'attr' => array('name' => 'remember_me'),
                'label' => 'Se souvenir de moi',
                'required' => false
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }

    public function getBlockPrefix()
    {
        return null;
    }
}
