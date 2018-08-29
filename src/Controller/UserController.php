<?php

namespace App\Controller;

use App\Entity\JobOffer;
use App\Entity\OfferType;
use App\Entity\Postulation;
use App\Entity\User;
use App\Form\AddJobOfferType;
use App\Form\ClosingType;
use App\Form\EditMyAccountType;
use App\Form\LoginType;
use App\Form\NewCredentialType;
use App\Form\NewPasswordType;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

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

        if ($form->isSubmitted() && $form->isValid()) {
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
        if ($user) {
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
        } else {
            throw $this->createNotFoundException('Une erreur est survenue durant l\'activation du compte');
        }
    }

    /**
     * @Route("se-connecter", name="login")
     * Description : Fonction qui se charge de la connexion selon Symfony
     */
    public function login(AuthenticationUtils $authenticationUtils)
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
        if ($request->isXmlHttpRequest()) {
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array(
                'email' => $request->getContent()
            ));

            //Vérification qu'il y a bien un utilisateur liée à l'adresse email envoyée
            if ($user) {

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
                    );

                $mailer->send($email);

                $user->setPassword($temporaryPassword);
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('notice', 'Un email contenant un mot de passe provisoire vous a été envoyé.');

                return new JsonResponse(array(
                    'status' => 'success',
                    'url' => $this->generateUrl('newPassword')
                ));
            } else
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

        if ($form->isSubmitted() && $form->isValid()) {
            //Vérification que l'utilisateur est bien le bon et qu'il le mot de passe correspond à celui de l'email
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(array(
                'username' => $form->get('username')->getData(),
                'password' => $form->get('oldPassword')->getData()
            ));

            if ($user) {
                $user->setPassword($encoder->encodePassword($user, $form['password']->getData()));

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Le mot de passe a été changé avec succès !');
            } else
                $form->addError(new FormError('Identifiants invalides. Vérifiez que le pseudo soit correcte et que l\'ancien mot de passe corressponde à celui qui vous été envoyé'));
        }

        return $this->render('user/newPassword.html.twig', array(
            'formNewPassword' => $form->createView()));
    }

    /**
     * @Route("ajouter-une-annonce", name="addOffer")
     * @Security("has_role('ROLE_USER')")
     */
    public function addOffer(Request $request, EntityManagerInterface $entity)
    {
        $jobOffer = new JobOffer();

        $form = $this->createForm(AddJobOfferType::class, $jobOffer);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $jobOffer = $form->getData();
            $user = $this->getUser();

            $offerType = $this->getDoctrine()->getRepository(OfferType::class)->findOneBy(array(
                'name' => $form->get('type')->getData()
            ));
            $jobOffer->setOfferType($offerType);
            $jobOffer->setUser($user);

            //Soit l'annonce est directement publiée, soit l'utilisateur l'a sauvegardée pour la publier plus tard
            if ($form->get('publish')->isClicked()) {
                //Ajout de la date de publication et de la date d'expiration
                $jobOffer->setPublicationDate(new \DateTime());
                $renewalDate = new \DateTime();
                $jobOffer->setRenewalDate($renewalDate->add(new \DateInterval($this->getParameter('datetime_interval'))));

                $jobOffer->setIsActive(true);
            } else
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
     * @Security("has_role('ROLE_USER')")
     * Description : Affiche toutes les annonces de l'utilisateur
     */
    public function manageOffers(EntityManagerInterface $entity, Request $request)
    {
        $user = $this->getUser();

        $jobOffer = $entity->getRepository(JobOffer::class)->findBy(array(
            'user' => $user
        ));

        if ($jobOffer) {
            $queryCountSearchJobOffer = $entity->createQueryBuilder();

            $queryCountSearchJobOffer->select('jobOffer')
                ->from('App:JobOffer', 'jobOffer')
                ->join('jobOffer.offerType', 'typeOffer')
                ->where('typeOffer.name = :name')
                ->andWhere('jobOffer.isActive = true')
                ->andWhere('jobOffer.closing IS NULL')
                ->setParameter('name', 'searchJob');

            $searchJob = $queryCountSearchJobOffer->getQuery()->getResult();

            $queryCountOfferJob = $entity->createQueryBuilder();
            $queryCountOfferJob->select('jobOffer')
                ->from('App:JobOffer', 'jobOffer')
                ->join('jobOffer.offerType', 'typeOffer')
                ->where('typeOffer.name = :name')
                ->andWhere('jobOffer.isActive = true')
                ->andWhere('jobOffer.closing IS NULL')
                ->setParameter('name', 'offerJob');

            $offerJob = $queryCountOfferJob->getQuery()->getResult();

            $offerToRenew = 0;

            //Calcul du nombre d'offres qui doivent être renouvelées
            foreach ($jobOffer as $offer) {
                if ($offer->getRenewalDate() && $offer->getRenewalDate()->diff($offer->getPublicationDate())->d <= $this->getParameter('days_limit') && $offer->getRenewalDate()->diff($offer->getPublicationDate())->m === 0)
                    $offerToRenew++;
            }
        } else {
            $searchJob = 0;
            $offerJob = 0;
            $offerToRenew = 0;
        }

        //Requête qui permet d'afficher toutes les offres d'emplois liées à l'utilisateur et qui sont des offres d'emplois actives
        $queryOfferJob = $entity->createQueryBuilder();
        $queryOfferJob->select('jobOffer')
            ->from('App:JobOffer', 'jobOffer')
            ->innerJoin('jobOffer.user', 'user', Expr\Join::WITH, $queryOfferJob->expr()->eq('jobOffer.user', $user->getId()))
            ->where('jobOffer.closing IS NULL')
            ->andWhere('jobOffer.isActive = true');

        //Si la requête renvoie bien quelque chose, affichage des offres en cours
        if (!empty($queryOfferJob->getQuery()->getResult())) {
            $formOfferJob = $this->createFormBuilder()
                ->add('offers', EntityType::class, array(
                    'class' => JobOffer::class,
                    'query_builder' => $queryOfferJob,
                ))
                ->getForm();

            if ($request->isXmlHttpRequest()) {
                //Recherche des éventuelles postulations par rapport à l'offre sélectionnée
                $queryPostulations = $entity->createQueryBuilder();
                $queryPostulations->select('postulation')
                    ->from('App:Postulation', 'postulation')
                    ->join('postulation.user', 'user')
                    ->join('postulation.jobOffer', 'offer')
                    ->addSelect('user.username')
                    ->addSelect('offer.title AS offerTitle')
                    ->where('postulation.jobOffer = :offerId')
                    ->setParameter('offerId', $request->get('idOffer'));

                return new JsonResponse(array(
                    'postulations' => $queryPostulations->getQuery()->getArrayResult()
                ));
            } else {
                //Recherche des éventuelles postulations par rapport à la première offre qui sera affichée dans la liste déroulante
                $queryPostulations = $entity->createQueryBuilder();
                $queryPostulations->select('postulation')
                    ->from('App:Postulation', 'postulation')
                    ->where('postulation.jobOffer = :jobOffer')
                    ->setParameter('jobOffer', $jobOffer[0]->getId());
            }

            $postulations = $queryPostulations->getQuery()->getResult();

            if (!empty($postulations))
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
     * @Route("details/{jobOfferTitle}/{username}", name="userDetail")
     * @Security("has_role('ROLE_USER')")
     */
    function showDetailsUser($username, $jobOfferTitle, EntityManagerInterface $entity)
    {
        //Récupération de l'utilisateur pour afficher ses détails
        $user = $entity->getRepository(User::class)->findOneBy(array(
            'username' => $username
        ));

        //Récupération de la postulation liée à l'utilisateur
        $queryPostulation = $entity->createQueryBuilder();
        $queryPostulation->select('postulation')
            ->from('App:Postulation', 'postulation')
            ->join('postulation.jobOffer', 'offer')
            ->where('offer.title = :offerTitle')
            ->andWhere('postulation.jobOffer = offer.id')
            ->andWhere('postulation.user = :user')
            ->setParameters(array(
                'offerTitle' => $jobOfferTitle,
                'user' => $user->getId()
            ));

        $postulation = $queryPostulation->getQuery()->getSingleResult();

        //Si la postulation a une date de réponse, cela veut dire que la personne a déjà répondu et qu'il ne faudra pas afficher les boutons "Accepter" ou "Rejeter" la candidature
        if (!is_null($postulation->getResponseDate()))
            return $this->render('user/userDetails.html.twig', array(
                'user' => $user,
                'postulationID' => $postulation->getId(),
                'alreadyResponded' => true
            ));
        else
            return $this->render('user/userDetails.html.twig', array(
                'user' => $user,
                'postulationID' => $postulation->getId(),
                'alreadyResponded' => false
            ));
    }

    /**
     * @Route("mon-compte", name="myAccount")
     * @Security("has_role('ROLE_USER')")
     * Description : Permet de modifier les informations de son compte
     */
    public function updateAccount(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder)
    {
        //Récupération de l'utilisateur
        $user = $this->getUser();

        $form = $this->createForm(EditMyAccountType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);

            //Si l'utilisateur à indiqué un nouveau mot de passe, encodage du mot de passe et affiliation à l'entié User
            if (!empty($form->get('password')->getData()))
                $user->setPassword($encoder->encodePassword($user, $form->get('password')->getData()));

            $entityManager->flush();
            $this->addFlash('success', 'Informations modifiées avec succès');
        }

        return $this->render('user/myAccount.html.twig', array(
            'form' => $form->createView(),
            'image' => $user->getUsername() . '/' . $user->getPicture()
        ));
    }

    /**
     * @Route("candidature", name="apply")
     * @Security("has_role('ROLE_USER')")
     * Description : Fonction qui se charge d'ajouter une postulation à une offre
     */
    public function apply(Request $request, EntityManagerInterface $entity)
    {
        if ($request->isXmlHttpRequest())
        {
            $jobOffer = $entity->getRepository(JobOffer::class)->findOneBy(array(
                'id' => $request->getContent()
            ));

            $user = $this->getUser();

            $checkPostulation = $entity->getRepository(Postulation::class)->findOneBy(array(
                'jobOffer' => $jobOffer,
                'user' => $user
            ));

            if(!$checkPostulation)
            {
                //Création d'une nouvelle postulation
                $postulation = new Postulation();

                $entity->persist($postulation);

                $postulation->setUser($user);
                $postulation->setJobOffer($jobOffer);
                $postulation->setPostulationDate(new \DateTime());
                $postulation->setStatus(false);

                $entity->flush();

                $this->addFlash('success', 'Votre postulation a été enregistré avec succès');

                return new JsonResponse(array(
                    'url' => $this->generateUrl('index')
                ));
            }
            else
            {
                $this->addFlash('warning', 'Vous ne pouvez pas postuler plusieurs fois à la même annonce');

                return new JsonResponse(array(
                    'url' => $this->generateUrl('index')
                ));
            }
        }
    }

    /**
     * @Route("rejeter", name="rejectCandidate")
     * @Security("has_role('ROLE_USER')")
     * Description : Met à jour une postulation (dans ce cas : rejet de la candidature)
     */
    public function rejectCandidate(Request $request, EntityManagerInterface $entity, \Swift_Mailer $mailer)
    {
        if ($request->isXmlHttpRequest()) {
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
                );

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
     * @Security("has_role('ROLE_USER')")
     * Description : Met à jour une postulation (dans ce cas : accepte la candidature)
     */
    public function acceptCandidate(Request $request, EntityManagerInterface $entity, \Swift_Mailer $mailer)
    {
        if ($request->isXmlHttpRequest()) {
            $postulation = $entity->getRepository(Postulation::class)->findOneBy(array(
                'id' => $request->get('postulationID'),
                'user' => $request->get('userID')
            ));

            dump($postulation);

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
                );

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

    /**
     * @Route("details-offre", name="getOfferDetails")
     * Description : va chercher les informations d'une offre pour l'affichage de ses détails
     */
    public function getOfferDetails(Request $request, EntityManagerInterface $entity)
    {
        if($request->isXmlHttpRequest())
        {
            $jobOffer = $entity->getRepository(JobOffer::class)->findOneBy(array(
               'id' => $request->get('id')
            ));

            return new JsonResponse(array(
                'title' => $jobOffer->getTitle(),
                'description' => $jobOffer->getDescription(),
                'category' => $jobOffer->getCategory()->getTitle(),
                'city' => $jobOffer->getCity()->getNpa() . ' ' . $jobOffer->getCity()->getName(),
                'publicationDate' => $jobOffer->getPublicationDate(),
                'isActive' => $jobOffer->getIsActive(),
                'isClosed' => $jobOffer->getClosing(),
                'id' => $jobOffer->getId()
            ));
        }
    }

    /**
     * @Route("renouveler-offre", name="renewOffer")
     * @Security("has_role('ROLE_USER')")
     * Description : S'occupe de renouveler les offres d'emplois sélectionnées dans la gestion de ses propres offres
     */
    public function renewOffer(Request $request, EntityManagerInterface $entity)
    {
        if($request->isXmlHttpRequest())
        {
            $id = explode(',', $request->get('data'));

            for($i = 0; $i < count($id); $i++)
            {
                $jobOffer = $entity->getRepository(JobOffer::class)->findOneBy(array(
                    'id' => $id[$i]
                ));

                $renewalDate = $jobOffer->getRenewalDate();

                $cloneRenewalDate = clone $renewalDate;
                $cloneRenewalDate->add(new \DateInterval($this->getParameter('datetime_interval')));

                $jobOffer->setRenewalDate($cloneRenewalDate);

                $entity->flush();
            }

            $this->addFlash('success', 'Toutes les annonces sélectionnées ont été renouvelées');

            return new JsonResponse(array(
               'url' => $this->generateUrl('manageOffers')
            ));
        }
    }

    /**
     * @Route("editer-une-offre/offre-num-{id}", name="editOffer")
     * @Security("has_role('ROLE_USER')")
     * Description : S'occupe de gérer la modification d'une offre
     */
    public function editOffer(Request $request, EntityManagerInterface $entity, $id)
    {
        $jobOffer = $entity->getRepository(JobOffer::class)->findOneBy(array(
            'id' => $id
        ));

        $form = $this->createForm(AddJobOfferType::class, $jobOffer);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $entity->persist($jobOffer);

            $entity->flush();

            $this->addFlash('success', 'Annonce modifiée avec succès');

            return $this->redirectToRoute('manageOffers');
        }

        return $this->render('user/editlOffer.html.twig', array(
            'editFormOffer' => $form->createView()
        ));
    }

    /**
     * @Route("cloturer-une-offre/offre-num-{id}", name="closeOffer")
     * @Security("has_role('ROLE_USER')")
     * Description : S'occupe de gérer les clôtures d'annonces
     */
    public function closeOffer(EntityManagerInterface $entity, Request $request, \Swift_Mailer $mailer, $id)
    {
        $form = $this->createForm(ClosingType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            //Récupérations des informations
            $closing = $form->getData();
            $entity->persist($closing);

            //Récupération de l'offre en cours de fermeture
            $jobOffer = $entity->getRepository(JobOffer::class)->findOneBy(array(
               'id' => $id
            ));

            $closing->setDate(new \DateTime('now'));

            //Fermeture de l'offre
            $jobOffer->setClosing($closing);
            $jobOffer->setIsActive(false);

            $entity->flush();

            //Création de l'email
            $email = (new \Swift_Message('Une candidature a été acceptée'))
                ->setSubject('Bourse Emploi - Caritas Jura')
                ->setFrom('info@bourse-emploi-jura.ch')
                ->setTo($this->getUser()->getEmail())
                ->setBody(
                    $this->renderView(
                        'emails/emailClosedOffer.html.twig',
                        array(
                            'jobOfferTitle' => $jobOffer->getTitle(),
                            'jobOfferDescription' => $jobOffer->getDescription(),
                            'jobOfferPublicationDate' => $jobOffer->getPublicationDate(),
                            'closingType' => $closing->getClosingType(),
                            'firstName' => $this->getUser()->getFirstName()
                        )
                    ),
                    'text/html'
                );

            $mailer->send($email);

            $this->addFlash('success', 'L\'annonce a été clôturée avec succès');

            return $this->redirectToRoute('manageOffers');
        }

        return $this->render('user/closeOffer.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
