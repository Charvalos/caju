<?php
/**
 * Created by PhpStorm.
 * User: projet7
 * Date: 27.07.2018
 * Time: 10:40
 */

namespace App\EventListener;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener
{
    private $em;
    private $flashMessage;

    public function __construct(EntityManagerInterface $entityManager, FlashBagInterface $flashBag)
    {
        $this->em = $entityManager;
        $this->flashMessage = $flashBag;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        //L'application ne va pas stocker les informations de dernière connexion si l'utilisateur est un utilisateur Admin
        if($user instanceof User)
        {
            $user->setLastLogin(new \DateTime());
            $this->flashMessage->add('success', 'Vous êtes maintenant connecté');

            $this->em->persist($user);
            $this->em->flush();
        }
    }
}