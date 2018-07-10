<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UserController extends AbstractController
{
    /**
     * @Route("ajouter-une-annonce", name="addOffer")
     */
    public function viewFormAddOffer()
    {
        $form = $this->createFormBuilder()
            ->add('title', TextType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Titre de l\'annonce'),
                'required' => true
            ))
            ->add('workAddress', ChoiceType::class, array(
                'choices' => array(
                    '2800 Delémont' => 'delemont',
                    '2854 Bassecourt' => 'bassecourt',
                    '2900 Porrentruy' => 'porrentruy'
                ),
                'label' => false,
                'required' => true
            ))
            ->add('category', ChoiceType::class, array(
                'choices' => array(
                    'Baby-sitting' => 'babysitting',
                    'Jardinage' => 'jardinage',
                    'Déménagement' => 'demenagement'
                ),
                'label' => false,
                'required' => true
            ))
            ->add('documents', FileType::class, array(
                'multiple' => true,
                'label' => 'Documents'
            ))
            ->add('description', TextareaType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Description de l\'annonce')
            ))
            ->add('add', SubmitType::class, array(
                'label' => 'Publier',
                'attr' => array('class' => 'btn btn-primary')
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'Sauvegarder',
                'attr' => array('class' => 'btn btn-primary')
            ))
            ->getForm();

        return $this->render('user/addOffer.html.twig', array(
            'addOfferForm' => $form->createView()
        ));
    }

    /**
     * @Route("gerer-mes-annonces", name="manageOffers")
     */
    public function manageOffers()
    {
        $form = $this->createFormBuilder()
            ->add('offers', ChoiceType::class, array(
                'choices' => array(
                    'Baby-Sitting : Recherche baby-sitter' => '1',
                    'Tonte de gazon : xxxxx' => '2'
                ),
                'label' => false
            ))
            ->getForm();

        return $this->render('user/manageMyOffers.html.twig', array(
            'selectOffer' => $form->createView()
        ));
    }

    /**
     * @Route("details", name="detail")
     */
    function showDetailsUser()
    {
        return $this->render('user/userDetails.html.twig');
    }

    /**
     * @Route("mon-compte", name="myAccount")
     */
    function showMyAccount()
    {
        return $this->render('user/myAccount.html.twig');
    }
}
