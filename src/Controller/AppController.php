<?php

namespace App\Controller;

use App\Entity\JobOffer;
use App\Form\FilterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
        //Création du formulaire
        $form = $this->createForm(FilterType::class);

        $offers = $this->getDoctrine()->getRepository(JobOffer::class)->findAll();

        return $this->render('user/listOffers.html.twig', array(
            'filterForm' => $form->createView(),
            'offers' => $offers
        ));
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
     * @Route("se-connecter", name="login")
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
     * @Route("detail-annonce", name="detailOffer")
     */
    public function viewDetailOffer()
    {
        return $this->render('user/detailOffer.html.twig');
    }
}
