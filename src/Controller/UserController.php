<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginType;
use App\Form\RegisterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class UserController
 * @package App\Controller
 */
class UserController extends AbstractController
{

    /**
     * @Route("inscription", name="registration")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param \Swift_Mailer $mailer
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     * Description : S'occupe de l'inscription d'une personne
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer)
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class);

        //Gestion des données renvoyées
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            //Encodage du mot de passe
            $password = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password)
                ->setIsActive(false);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);

            //Envoie d'une requête pour vérifier que le pseudo n'existe déjà pas
            $checkUser = $this->getDoctrine()->getRepository(User::class)->findOneBy(array(
                'username' => $user->getUsername()
            ));

            //Si la requête ne renvoie rien, cela indique que le pseudo est libre
            if(!$checkUser)
            {
                //Génération du hash et de l'URL pour l'email de confirmation
                $hash = sha1(random_int(1, 1000));
                $user->setHash($hash);

                $email = (new \Swift_Message('Email'))
                    ->setSubject('Bourse Emploi - Caritas Jura')
                    ->setFrom("info@bourse-emploi-jura.ch ")
                    ->setTo($user->getEmail())
                    ->setBody(
                        $this->renderView(
                            'emails/emailRegistration.html.twig',
                            array(
                                'firstName' => $user->getFirstName(),
                                'hash' => $user->getHash(),
                                'email' => $user->getEmail()
                            )
                        )
                    )
                    ->setContentType('text/html');

                $entityManager->flush();

                $this->addFlash('success', 'Inscription réussie avec succès ! Veuillez activer votre compte (vérifier vos spams)');
                $mailer->send($email);

                return $this->redirectToRoute('index');
            }
            else
            {
                $form->get('username')->addError(new FormError('Pseudo déjà existant'));
            }
        }

        return $this->render('utils/registration.html.twig', array(
            'registerForm' => $form->createView(),
        ));
    }

    /**
     * @Route("activation-compte-{hash}-{email}", name="checkEmail")
     * @param $hash
     * @param $email
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * Description : Active ou non compte
     */
    public function checkEmail($hash, $email)
    {
        //Vérification de l'email de confirmation avec le hash et l'email
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array(
            'email' => $email,
            'hash' => $hash
        ));

        //Si l'utilisateur a bien été trouvé, activation du compte
        if($user)
        {
            $user->setHash(null)
                ->setIsActive(true);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Votre compte a bien été activé.');

            return $this->redirectToRoute('index');
        }
        else
        {
            throw $this->createNotFoundException('Une erreur est survenue durant l\'activation du compte');
        }
    }

    /**
     * @Route("se-connecter", name="login")
     */
    public function connection(Request $request, AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('utils/login.html.twig', array(
            'lastUsername' => $lastUsername,
            'error' => $error
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
