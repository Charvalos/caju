<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\City;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('place', EntityType::class, array(
                'class' => City::class,
                'query_builder' => function(EntityRepository $er){
                    return $er->createQueryBuilder('cities')
                        ->orderBy('cities.npa', 'ASC');
                }
            ))
            ->add('date', DateType::class, array(
                'format' => 'dd MM yyyy'
            ))
            ->add('category', EntityType::class, array(
                'class' => Category::class,
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('categories')
                        ->orderBy('categories.title', 'ASC');
                }
            ))
            ->add('filter', SubmitType::class, array(
                'label' => 'Filtrer',
                'attr' => array('class' => 'btn btn-primary')
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
