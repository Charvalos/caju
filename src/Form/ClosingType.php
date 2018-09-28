<?php

namespace App\Form;

use App\Entity\Closing;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClosingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('closingType', EntityType::class, array(
                'class' => \App\Entity\ClosingType::class,
                'query_builder' => function(EntityRepository $er){
                    return $er->createQueryBuilder('listClosingType')
                        ->where('listClosingType.name != :param')
                        ->setParameter('param', 'Admin')
                        ->orderBy('listClosingType.name', 'ASC');
                },
                'label' => false,
                'placeholder' => 'Raison de la clôture de l\'annonce'
            ))
            ->add('close', SubmitType::class, array(
                'label' => 'Clôturer l\'annonce',
                'attr' => array('class' => 'btn btn-primary')
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Closing::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return null;
    }
}
