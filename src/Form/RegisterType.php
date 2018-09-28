<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\District;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Yaml\Tests\A;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, array(
                'attr' => array('placeholder' => 'Pseudo'),
                'label' => false
            ))
            ->add('password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'Les deux mots de passe doivent être identiques',
                'required' => true,
                'first_options' => array('label' => false, 'attr' => array('placeholder' => 'Mot de passe')),
                'second_options' => array('label' => false, 'attr' => array('placeholder' => 'Confimer le mot de passe'))
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
                    'placeholder' => 'Téléphone N°1 (obligatoire)',
                ),
                'label' => false
            ))
            ->add('phoneN2', TelType::class, array(
                'attr' => array(
                    'placeholder' => 'Téléphone N°2 (facultatif)',
                    ),
                'label' => false,
                'empty_data' => '0000000000',
                'required' => false
            ))
            ->add('address', TextType::class, array(
                'attr' => array('placeholder' => 'Rue'),
                'label' => false
            ))
            ->add('birthdate', BirthdayType::class, array(
                'widget' => 'single_text',
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
                'attr' => array(
                    'data-toggle' => 'popover',
                    'data-trigger' => 'hover',
                    'data-content' => 'La localité correspond au lieu de travail',
                    'data-placement' => 'top',
                    'class' => 'select'
                )
            ))
            ->add('cgu', CheckboxType::class, array(
                'label' => 'J\'ai lu et j\'accepte les conditions générales d\'utilisation',
                'mapped' => false
            ))
            ->add('register', SubmitType::class, array(
                'attr' => array('class' => 'btn btn-primary'),
                'label' => 'S\'inscrire'
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}
