<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @Route("annonces", name="offers")
     */
    public function index()
    {
        return $this->render('user/listOffers.html.twig');
    }

    /**
     * @Route("cheque-emploi", name="description")
     */
    public function viewDescription()
    {
        return $this->render('utils/description.html.twig');
    }

    /**
     * @Route("conditions-generales-utilisation", name="conditions")
     */
    public function viewConditions()
    {
        return $this->render('utils/conditions.html.twig');
    }

    /**
     * @Route("se-connecter", name="connection")
     */
    public function viewFormConnection()
    {
        $form = $this->createFormBuilder()
            ->add('pseudo', TextType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Pseudo'),
                'required' => true
            ))
            ->add('password', PasswordType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Mot de passe'),
                'required' => true
            ))
            ->add('stayConnected', CheckboxType::class, array(
                'label' => 'Se souvenir de moi'
            ))
            ->add('connect', SubmitType::class, array(
                'label' => 'Se connecter',
                'attr' => array('class' => 'btnSubmit btn-primary')
            ))
            ->getForm();

        return $this->render('utils/connection.html.twig', array(
            'connectionForm' => $form->createView()
        ));
    }

    /**
     * @Route("inscription", name="registration")
     */
    public function viewFormRegister()
    {
        $form = $this->createFormBuilder()
            ->add('pseudo', TextType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Pseudo'),
                'required' => true
            ))
            ->add('password', PasswordType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Mot de passe'),
                'required' => true
            ))
            ->add('confirmPassword', PasswordType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Confirmer le mot de passe'),
                'required' => true
            ))
            ->add('lastName', TextType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Nom'),
                'required' => true
            ))
            ->add('firstName', TextType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Prénom'),
                'required' => true
            ))
            ->add('birthDate', DateType::class, array(
                'label' => false,
                'label_attr' => array('class' => 'input-group-text'),
                'required' => true,
                'format' => 'dd MMMM yyyy',
            ))
            ->add('email', EmailType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Email'),
                'required' => true
            ))
            ->add('phoneN1', TelType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Téléphone fixe'),
            ))
            ->add('phoneN2', TelType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Téléphone mobile')
            ))
            ->add('address', TextType::class, array(
                'label' => false,
                'attr' => array('placeholder' => 'Rue'),
                'required' => true
            ))
            ->add('locality', ChoiceType::class, array(
                'choices' => array(
                    '2800 Delémont' => 'delemont',
                    '2854 Bassecourt' => 'bassecourt',
                    '2900 Porrentruy' => 'porrentruy'
                ),
                'label' => false
            ))
            ->add('cguChecked', CheckboxType::class, array(
                'label' => 'J\'ai lu et j\'accepte les CGU',
                'required' => true
            ))
            ->add('register', SubmitType::class, array(
                'label' => 'S\'inscrire',
                'attr' => array('class' => 'btn btn-primary')
            ))
            ->getForm();

        return $this->render('utils/inscription.html.twig', array(
            'registerForm' => $form->createView()
        ));
    }

    /**
     * @Route("detail-annonce", name="detailOffer")
     */
    public function viewDetailOffer()
    {
        return $this->render('user/detailOffer.html.twig');
    }
}
