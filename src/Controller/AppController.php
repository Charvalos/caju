<?php

namespace App\Controller;

use App\Entity\JobOffer;
use App\Form\FilterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @Route("annonces", name="offers")
     */
    public function index(Request $request)
    {
        //Si la page qui appelé la page d'index correspond à "mon-compte", cela veut dire que l'utilisateur a modifié son mot de passe et a été déconnecté
        if(strpos($request->headers->get('referer'), 'mon-compte'))
            $this->addFlash('warning', 'Vous avez modifié votre mot de passe. Veuillez vous reconnectez.');

        //Création du formulaire de filtrage
        $form = $this->createForm(FilterType::class);

        $offers = $this->getDoctrine()->getRepository(JobOffer::class)->findAll();

        return $this->render('user/listOffers.html.twig', array(
            'filterForm' => $form->createView(),
            'offers' => $offers,
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
     * @Route("detail-annonce", name="detailOffer")
     */
    public function viewDetailOffer()
    {
        return $this->render('user/detailOffer.html.twig');
    }
}
