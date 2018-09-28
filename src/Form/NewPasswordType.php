<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, array(
                'attr' => array('placeholder' => 'Pseudo'),
                'label' => false
            ))
            ->add('oldPassword', PasswordType::class, array(
                'attr' => array('placeholder' => 'Mot de passe provisoire'),
                'label' => false
            ))
            ->add('password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'Les deux mots de passe doivent être identiques',
                'required' => true,
                'first_options' => array('label' => false, 'attr' => array('placeholder' => 'Nouveau mot de passe')),
                'second_options' => array('label' => false, 'attr' => array('placeholder' => 'Confimer le nouveau mot de passe'))
            ))
            ->add('updatePassword', SubmitType::class, array(
                'attr' => array('class' => 'btn btn-primary'),
                'label' => 'Confirmer'
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
