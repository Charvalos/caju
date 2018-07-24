<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginType;
use App\Form\NewCredentialType;
use App\Form\NewPasswordType;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
     * Description : S'occupe de la phase d'inscription de l'utilisateur
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder, \Swift_Mailer $mailer)
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        //Gestion des données renvoyées
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            //Récupération des données envoyées par le formulaire
            $user = $form->getData();

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

            //Vérification également que l'email n'est déjà pas utilisé
            $checkEmail = $this->getDoctrine()->getRepository(User::class)->findOneBy(array(
                'email' => $user->getEmail()
            ));

            //Si le pseudo et l'email ne sont pas utilisés
            if(!$checkUser)
            {
                if(!$checkEmail)
                {
                    //Génération du hash et de l'URL pour l'email de confirmation
                    $hash = sha1(random_int(1, 1000));
                    $user->setHash($hash);

                    //Lien de confirmation qui sera envoyé à l'utilisateur
                    $slug = $user->getHash() . '&' . $user->getEmail();

                    $email = (new \Swift_Message('Email'))
                        ->setSubject('Bourse Emploi - Caritas Jura')
                        ->setFrom("info@bourse-emploi-jura.ch ")
                        ->setTo($user->getEmail())
                        ->setBody(
                            $this->renderView(
                                'emails/emailRegistration.html.twig',
                                array(
                                    'firstName' => $user->getFirstName(),
                                    'slug' => $slug
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
                    $form->get('email')->addError(new FormError('L\'adresse email est déjà utilisée'));
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
     * @Route("activation-compte-{slug}", name="checkEmail")
     * Description : Fonction qui se charge de la confirmation de l'inscription
     */
    public function checkEmail($slug)
    {
        $string = explode('&', $slug);

        $hash = $string[0];
        $email = $string[1];

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
     * Description : Fonction qui se charge de la connexion selon Symfony
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils)
    {
        //Récupération des éventuelles erreurs (p.ex : mauvais identifiants)
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(LoginType::class, [
            'username' => $lastUsername
        ]);

        $formNewCredential = $this->createForm(NewCredentialType::class);

        return $this->render('utils/login.html.twig', array(
            'error' => $error,
            'loginForm' => $form->createView(),
            'newCredentialForm' => $formNewCredential->createView()
        ));
    }

    /**
     * @Route("demande-nouveau-mot-de-passe", name="requestNewPassword")
     * Description : Fonction qui se charge de vérifier que l'email est correct et envoie un mot de provisoire à l'utilisateur via email
     */
    public function requestPasswordReset(Request $request, \Swift_Mailer $mailer, EntityManagerInterface $entityManager)
    {
        if($request->isXmlHttpRequest())
        {
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array(
               'email' => $request->getContent()
            ));

            //Vérification qu'il y a bien un utilisateur liée à l'adresse email envoyée
            if($user)
            {

                $temporaryPassword = sha1(rand(1, 1000));

                $email = (new \Swift_Message('Nouveau mot de passe'))
                    ->setSubject('Bourse Emploi - Caritas Jura')
                    ->setFrom('info@bourse-emploi-jura.ch')
                    ->setTo($user->getEmail())
                    ->setBody(
                        $this->renderView(
                            'emails/emailNewPassword.html.twig',
                            array('password' => $temporaryPassword)
                        ),
            'text/html'
                    )
                ;

                $mailer->send($email);

                $user->setPassword($temporaryPassword);
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('notice', 'Un email contenant un mot de passe provisoire vous a été envoyé.');

                return new JsonResponse(array(
                    'status' => 'success',
                    'url' => $this->generateUrl('newPassword')
                ));
            }
            else
                return new JsonResponse(array(
                    'status' => 'error'
                ));
        }
    }

    /**
     * @Route("nouveau-mot-de-passe", name="newPassword")
     * Description : S'occupe de gérer la demande de réinitialisation du mot de passe
     */
    public function newPassword(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder)
    {

        $form = $this->createForm(NewPasswordType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            //Vérification que l'utilisateur est bien le bon et qu'il le mot de passe correspond à celui de l'email
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array(
                'username' => $form['username']->getData(),
                'password' => $form['oldPassword']->getData()
            ));
            
            if($user)
            {
                $user->setPassword($encoder->encodePassword($user, $form['password']->getData()));

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Le mot de passe a été changé avec succès !');
            }
            else
                $form->addError(new FormError('Identifiants invalides. Vérifiez que le pseudo soit correcte et que l\'ancien mot de passe corressponde à celui qui vous été envoyé'));
        }

        return $this->render('user/newPassword.html.twig', array(
            'formNewPassword' => $form->createView()));
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
