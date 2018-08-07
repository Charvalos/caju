<?php

namespace App\Controller;

use App\Entity\JobOffer;
use App\Entity\OfferType;
use App\Entity\Postulation;
use App\Entity\User;
use App\Form\AddJobOfferType;
use App\Form\EditMyAccountType;
use App\Form\EditOfferType;
use App\Form\LoginType;
use App\Form\NewCredentialType;
use App\Form\NewPasswordType;
use App\Form\RegisterType;
use App\Form\UploadProfileImageType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use phpDocumentor\Reflection\Types\Array_;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DomCrawler\Field\InputFormField;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Constraints\DateTime;

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
                ->setIsActive(false)
                ->setRegistrationDate(new \DateTime())
                ->setRoles('ROLE_USER');

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);

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

            $this->addFlash('success', 'Inscription réussie avec succès ! Veuillez activer votre compte (vérifiez vos spams)');
            $mailer->send($email);

            return $this->redirectToRoute('index');
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

            //Création des répertoirs propres à l'utilisateur
            $fileSystem = new Filesystem();
            $profileImagesDir = $this->getParameter('kernel.project_dir') . '/public/profile_images/';
            $profileDocumentsDir = $this->getParameter('kernel.project_dir') . '/public/documents/';
            $fileSystem->mkdir($profileImagesDir . $user->getUsername());
            $fileSystem->mkdir($profileDocumentsDir . $user->getUsername());

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

        $this->addFlash('success', 'Vous êtes maintenant connecté');

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
                'username' => $form->get('username')->getData(),
                'password' => $form->get('oldPassword')->getData()
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
    public function addOffer(Request $request, EntityManagerInterface $entity)
    {
        $jobOffer = new JobOffer();

        $form = $this->createForm(AddJobOfferType::class, $jobOffer);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $jobOffer = $form->getData();
            $user = $this->getUser();

            $offerType = $this->getDoctrine()->getRepository(OfferType::class)->findOneBy(array(
                'name' => $form->get('type')->getData()
            ));
            $jobOffer->setOfferType($offerType);
            $jobOffer->setUser($user);

            //Soit l'annonce est directement publiée, soit l'utilisateur l'a sauvegardée pour la publier plus tard
            if($form->get('publish')->isClicked())
            {
                //Ajout de la date de publication et de la date d'expiration
                $jobOffer->setPublicationDate(new \DateTime());
                $renewalDate = new \DateTime();
                $jobOffer->setRenewalDate($renewalDate->add(new \DateInterval('P2M')));

                $jobOffer->setIsActive(true);
            }
            else
                $jobOffer->setIsActive(false);

            $entity->persist($jobOffer);
            $entity->flush();

            $this->addFlash('success', 'Votre annonce a été ajoutée avec succès');

            return $this->redirect($this->generateUrl('index'));
        }

        return $this->render('user/addOffer.html.twig', array(
            'addOfferForm' => $form->createView()
        ));
    }

    /**
     * @Route("gerer-mes-annonces", name="manageOffers")
     * Description : Affiche toutes les annonces de l'utilisateur
     */
    public function manageOffers(EntityManagerInterface $entity, Request $request)
    {
        $user = $this->getUser();

        $jobOffer = $entity->getRepository(JobOffer::class)->findBy(array(
            'user' => $user
        ));

        if($jobOffer)
        {
            //Comptage des différentes offres de type "Offre d'emplois" ou "Recherche d'emploi"
            $searchJob = $entity->getRepository(JobOffer::class)->count(array(
                'offerType' => $entity->getRepository(OfferType::class)->findOneBy(array(
                    'name' => 'searchJob'
                )),
                'isActive' => true
            ));

            $offerJob = $entity->getRepository(JobOffer::class)->count(array(
                'offerType' =>$entity->getRepository(OfferType::class)->findOneBy(array(
                    'name' => 'offerJob'
                )),
                'isActive' => true
            ));

            $offerToRenew = 0;

            //Calcul du nombre d'offres qui doivent être renouvelées
            foreach ($jobOffer as $offer)
            {
                if($offer->getRenewalDate() && $offer->getRenewalDate()->diff($offer->getPublicationDate())->d <= 14 && $offer->getRenewalDate()->diff($offer->getPublicationDate())->m === 0)
                    $offerToRenew++;
            }
        }
        else
        {
            $searchJob = 0;
            $offerJob = 0;
            $offerToRenew = 0;
        }

        //Requête qui permet d'afficher toutes les offres d'emplois liées à l'utilisateur qui sont des offres d'emplois actives
        $queryOfferJob = $entity->createQueryBuilder();
        $queryOfferJob->select('jobOffer')
            ->from('App:JobOffer', 'jobOffer')
            ->innerJoin('jobOffer.user', 'user', Expr\Join::WITH, $queryOfferJob->expr()->eq('jobOffer.user', $user->getId()))
            /*->innerJoin('jobOffer.offerType', 'type', Expr\Join::WITH, $queryOfferJob->expr()->eq('jobOffer.offerType', $entity->getRepository(OfferType::class)->findOneBy(array(
                'name' => 'offerJob'
            ))->getId()))*/
            ->where('jobOffer.closing IS NULL')
            ->andWhere('jobOffer.isActive = true');

        //Si la requête renvoie bien quelque chose, affichage des offres en cours
        if(!empty($queryOfferJob->getQuery()->getResult()))
        {
            $formOfferJob = $this->createFormBuilder()
                ->add('offers', EntityType::class, array(
                    'class' => JobOffer::class,
                    'query_builder' => $queryOfferJob,
                ))
                ->getForm();

            //Recherche des éventuelles postulations par rapport aux offres
            $queryPostulations = $entity->createQueryBuilder();
            $queryPostulations->select('postulation')
                ->from('App:Postulation', 'postulation')
                ->where('postulation.jobOffer IN (:jobOffer)')
                ->setParameter('jobOffer', $jobOffer);

            $postulations = $queryPostulations->getQuery()->getResult();

            if(!empty($postulations))
                return $this->render('user/manageMyOffers.html.twig', array(
                    'selectOffer' => $formOfferJob->createView(),
                    'postulations' => $user->getPostulations(),
                    'offerPostulations' => $postulations,
                    'jobOffers' => $jobOffer,
                    'searchJob' => $searchJob,
                    'offerJob' => $offerJob,
                    'toRenew' => $offerToRenew
                ));
            else
                return $this->render('user/manageMyOffers.html.twig', array(
                    'selectOffer' => $formOfferJob->createView(),
                    'postulations' => $user->getPostulations(),
                    'jobOffers' => $jobOffer,
                    'searchJob' => $searchJob,
                    'offerJob' => $offerJob,
                    'toRenew' => $offerToRenew
                ));
        }

        return $this->render('user/manageMyOffers.html.twig', array(
            'postulations' => $user->getPostulations(),
            'jobOffers' => $jobOffer,
            'searchJob' => $searchJob,
            'offerJob' => $offerJob,
            'toRenew' => $offerToRenew
        ));
    }

    /**
     * @Route("details/{username}", name="userDetail")
     */
    function showDetailsUser($username, EntityManagerInterface $entity)
    {
        //Récupération de l'utilisateur pour afficher ses détails
        $user = $entity->getRepository(User::class)->findOneBy(array(
            'username' => $username
        ));

        //Récupération de l'ID de la postulation
        $postulationId = $this->get('session')->get('id');

        dump($postulationId);

        $postulation = $entity->getRepository(Postulation::class)->findOneBy(array(
           'id' => $postulationId
        ));

        if(!is_null($postulation->getResponseDate()))
            return $this->render('user/userDetails.html.twig', array(
                'user' => $user,
                'postulationID' => $postulationId,
                'alreadyResponded' => true
            ));
        else
            return $this->render('user/userDetails.html.twig', array(
                'user' => $user,
                'postulationID' => $postulationId,
                'alreadyResponded' => false
            ));
    }

    /**
     * @Route("mon-compte", name="myAccount")
     * Description : Permet de modifier les informations de son compte
     */
    public function updateAccount(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder)
    {
        //Récupération de l'utilisateur
        $user = $this->getUser();

        $form = $this->createForm(EditMyAccountType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $entityManager->persist($user);

            //Si l'utilisateur à indiqué un nouveau mot de passe, encodage du mot de passe et affiliation à l'entié User
            if(!empty($form->get('password')->getData()))
            {
                $user->setPassword($encoder->encodePassword($user, $form->get('password')->getData()));

                $entityManager->flush();
            }

            $entityManager->flush();
            $this->addFlash('success', 'Informations modifiées avec succès');
        }

        return $this->render('user/myAccount.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("candidature", name="apply")
     * Description : Fonction qui se charge d'ajouter une postulation à une offre
     */
    public function apply(Request $request, EntityManagerInterface $entity)
    {
        if($request->isXmlHttpRequest())
        {
            //Création d'une nouvelle postulation
            $postulation = new Postulation();

            $user = $this->getUser();
            $jobOffer = $entity->getRepository(JobOffer::class)->findOneBy(array(
                'id' => $request->getContent()
            ));

            $entity->persist($postulation);

            $postulation->setUser($user);
            $postulation->setJobOffer($jobOffer);
            $postulation->setPostulationDate(new \DateTime());
            $postulation->setStatus(false);

            $entity->flush();

            $this->addFlash('success', 'Votre postulation a été enregistré avec succès');

            return new JsonResponse(array(
                'status' => 'success',
                'url' => $this->generateUrl('index')
            ));
        }
    }

    /**
     * @Route("rejeter", name="rejectCandidate")
     * Description : Met à jour une postulation (dans ce cas : rejet de la candidature)
     */
    public function rejectCandidate(Request $request, EntityManagerInterface $entity, \Swift_Mailer $mailer)
    {
        if($request->isXmlHttpRequest())
        {
            $postulation = $entity->getRepository(Postulation::class)->findOneBy(array(
                'id' => $request->get('postulationID'),
                'user' => $request->get('userID')
            ));

            $entity->persist($postulation);

            $postulation->setResponseDate(new \DateTime());

            //Récupérations de l'utilsateur candidat afin d'avoir les informations nécessaires pour l'envoi du mail
            $candidat = $entity->getRepository(User::class)->findOneBy(array(
                'id' => $request->get('userID')
            ));

            //Récupération également des informations concernant l'offre concernée
            $jobOffer = $entity->getRepository(JobOffer::class)->findOneBy(array(
                'id' => $postulation->getJobOffer()->getId()
            ));

            //Création de l'email
            $email = (new \Swift_Message('Une candidature a été refusée'))
                ->setSubject('Bourse Emploi - Caritas Jura')
                ->setFrom('info@bourse-emploi-jura.ch')
                ->setTo($candidat->getEmail())
                ->setBody(
                    $this->renderView(
                        'emails/emailPostulationRejected.html.twig',
                        array(
                            'jobOfferTitle' => $jobOffer->getTitle(),
                            'jobOfferDescription' => $jobOffer->getDescription(),
                            'postulationDate' => $postulation->getPostulationDate(),
                            'firstName' => $candidat->getFirstName()
                        )
                    ),
                    'text/html'
                )
            ;

            $mailer->send($email);

            $entity->flush();

            $this->addFlash('success', 'La candidature a bien été refusée');

            return new JsonResponse(array(
                'status' => 'success',
                'url' => $this->generateUrl('manageOffers')
            ));
        }
    }

    /**
     * @Route("accepter", name="acceptCandidate")
     * Description : Met à jour une postulation (dans ce cas : accepte la candidature)
     */
    public function acceptCandidate(Request $request, EntityManagerInterface $entity, \Swift_Mailer $mailer)
    {
        if($request->isXmlHttpRequest())
        {
            $postulation = $entity->getRepository(Postulation::class)->findOneBy(array(
                'id' => $request->get('postulationID'),
                'user' => $request->get('userID')
            ));

            $entity->persist($postulation);

            $postulation->setResponseDate(new \DateTime());
            $postulation->setStatus(true);

            $entity->flush();

            //Récupérations de l'utilsateur candidat afin d'avoir les informations nécessaires pour l'envoi du mail
            $candidat = $entity->getRepository(User::class)->findOneBy(array(
                'id' => $request->get('userID')
            ));

            //Récupération également des informations concernant l'offre concernée
            $jobOffer = $entity->getRepository(JobOffer::class)->findOneBy(array(
               'id' => $postulation->getJobOffer()->getId()
            ));

            $user = $this->getUser();

            //Création de l'email
            $email = (new \Swift_Message('Une candidature a été acceptée'))
                ->setSubject('Bourse Emploi - Caritas Jura')
                ->setFrom('info@bourse-emploi-jura.ch')
                ->setTo($candidat->getEmail())
                ->setBody(
                    $this->renderView(
                        'emails/emailPostulationAccepted.html.twig',
                        array(
                            'jobOfferTitle' => $jobOffer->getTitle(),
                            'jobOfferDescription' => $jobOffer->getDescription(),
                            'postulationDate' => $postulation->getPostulationDate(),
                            'firstName' => $candidat->getFirstName(),
                            'username' => $user->getUsername()
                        )
                    ),
                    'text/html'
                )
            ;

            $mailer->send($email);

            $this->addFlash('success', 'La candidature a été accepté.');

            return new JsonResponse(array(
                'status' => 'success',
                'url' => $this->generateUrl('manageOffers')
            ));
        }

        return new JsonResponse(array(
            'status' => 'success',
            'url' => $this->generateUrl('manageOffers')
        ));
    }
}
