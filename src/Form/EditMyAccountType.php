<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class EditMyAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
                ),
                'label' => false
            ))
            ->add('phoneN2', TelType::class, array(
                'attr' => array(
                    'placeholder' => 'Téléphone mobile',
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
                        ->join('listCities.district', 'district')
                        ->addSelect('district')
                        ->groupBy('listCities.name')
                        ->orderBy('listCities.npa', 'ASC');
                },
                'group_by' => function(City $city){
                    return $city->getDistrict()->getName();
                },
                'attr' => array('class' => 'selectCities')
            ))
            ->add('pictureFile', VichImageType::class, array(
                'required' => false,
                'download_link' => false,
                'allow_delete' => false,
                'download_uri' => false,
                'image_uri' => false,
                'label' => false,
                'attr' => array(
                    'placeholder' => 'Téléchager une image de profil'
                )
            ))
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
