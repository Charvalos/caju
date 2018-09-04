<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class EditMyAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', PasswordType::class, array(
                'attr' => array('placeholder' => 'Nouveau mot de passe'),
                'label' => false,
                'required' => false,
                'mapped' => false
            ))
            ->add('email', EmailType::class, array(
                'attr' => array('placeholder' => 'Email'),
                'label' => false
            ))
            ->add('firstName', TextType::class, array(
                'attr' => array('placeholder' => 'Prénom'),
                'label' => false
            ))
            ->add('lastName', TextType::class, array(
                'attr' => array('placeholder' => 'Nom'),
                'label' => false
            ))
            ->add('phoneN1', TelType::class, array(
                'attr' => array(
                    'placeholder' => 'Téléphone fixe',
                    'maxlength' => 10
                ),
                'label' => false
            ))
            ->add('phoneN2', TelType::class, array(
                'attr' => array(
                    'placeholder' => 'Téléphone mobile',
                    'maxlength' => 10
                ),
                'label' => false
            ))
            ->add('address', TextType::class, array(
                'attr' => array('placeholder' => 'Rue'),
                'label' => false
            ))
            ->add('birthdate', BirthdayType::class, array(
                'widget' => 'single_text'
            ))
            ->add('city', EntityType::class, array(
                'class' => City::class,
                'query_builder' => function(EntityRepository $er){
                    return $er->createQueryBuilder('listCities')
                        ->orderBy('listCities.npa', 'ASC');
                }
            ))
            /*->add('pictureFile', VichImageType::class, array(
                'data_class' => null,
                'required' => false,
                'attr' => array('placeholder' => 'Télécharger une image de profil (120x120)'),
                'label' => false,
                'mapped' => false
            ))*/
            ->add('update', SubmitType::class, array(
                'attr' => array('class' => 'btn btn-primary'),
                'label' => 'Sauvegarder'
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
