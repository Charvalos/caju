<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @Route("inscription", name="registration")
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        //Gestion des données renvoyées
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            //Encodage du mot de passe
            $password = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            $user->setIsActive(false);
            //Random hash

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Inscription réussie avec succès !');

            return $this->redirectToRoute('index');
        }

        return $this->render('utils/registration.html.twig', array(
            'registerForm' => $form->createView(),
        ));
    }

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
