<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\City;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('city', EntityType::class, array(
                'class' => City::class,
                'query_builder' => function(EntityRepository $entityRepository){
                    return $entityRepository->createQueryBuilder('listCities')
                        ->orderBy('listCities.npa', 'ASC');
                },
                'label' => false,
                'placeholder' => 'Localité',
                'required' => false,
            ))
            ->add('date', DateType::class, array(
                'widget' => 'single_text',
                'required' => false
            ))
            ->add('category', EntityType::class, array(
                'class' => Category::class,
                'query_builder' => function(EntityRepository $entityRepository){
                    return $entityRepository->createQueryBuilder('listCategories')
                        ->orderBy('listCategories.title', 'ASC');
                },
                'label' => false,
                'placeholder' => 'Catégorie',
                'required' => false
            ))
            ->add('filter', ButtonType::class, array(
                'label' => 'Filtrer',
                'attr' => array(
                    'class' => 'btn btn-primary'
                )
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
        ]);
    }

    public function getBlockPrefix()
    {
        return null;
    }
}
