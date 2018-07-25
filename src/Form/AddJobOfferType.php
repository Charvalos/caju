<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\City;
use App\Entity\JobOffer;
use Doctrine\ORM\EntityRepository;
use function foo\func;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddJobOfferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Titre de l\'annonce')
            ))
            ->add('description', TextareaType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Description de l\'annonce')
            ))
            ->add('documents', DocumentType::class, array(
                'label' => false
            ))
            ->add('category', EntityType::class, array(
                'class' => Category::class,
                'query_builder' => function(EntityRepository $entityRepository){
                    return $entityRepository->createQueryBuilder('listCategories')
                        ->orderBy('listCategories.title', 'ASC');
                }
            ))
            ->add('city', EntityType::class, array(
                'class' => City::class,
                'query_builder' => function(EntityRepository $entityRepository){
                    return $entityRepository->createQueryBuilder('listCities')
                        ->orderBy('listCities.npa', 'ASC');
                }
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'Sauvegarder',
                'attr' => array('class' => 'btn btn-secondary')
            ))
            ->add('publish', SubmitType::class, array(
                'label' =>'Publier',
                'attr' => array('class' => 'btn btn-primary')
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => JobOffer::class,
        ]);
    }
}