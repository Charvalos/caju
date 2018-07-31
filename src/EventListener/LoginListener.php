<?php
/**
 * Created by PhpStorm.
 * User: projet7
 * Date: 27.07.2018
 * Time: 10:40
 */

namespace App\EventListener;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        //Récupération de l'utilisateur qui vient de se connecter
        $user = $event->getAuthenticationToken()->getUser();

        //Actualisation de la date et heure de connexion
        $user->setLastLogin(new \DateTime());

        //Modification dans la BDD
        $this->em->persist($user);
        $this->em->flush();
    }
}