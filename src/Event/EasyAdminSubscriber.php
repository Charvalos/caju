<?php
namespace App\Event;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    protected $mailer;
    protected $twig;

    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig_Environment)
    {
        $this->twig = $twig_Environment;
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            'easy_admin.post_remove' => 'onPostRemove',
        ];
    }

    public function onPostRemove(GenericEvent $event)
    {
        $entity = $event->getSubject();

        if($entity instanceof User)
        {
            $email = (new \Swift_Message('Suppression du compte'))
                ->setFrom($this->getParameter('email'))
                ->setTo($entity->getEmail())
                ->setBody(
                    $this->twig->render('emails/emailAccountDeleted.html.twig', array(
                        'username' => $entity->getUsername()
                    )),
                    'text/html'
                );

            $this->mailer->send($email);
        }
    }
}