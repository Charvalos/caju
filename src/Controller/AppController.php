<?php

namespace App\Controller;

use App\Entity\JobOffer;
use App\Entity\OfferType;
use App\Entity\Postulation;
use App\Form\FilterType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @Route("annonces", name="offers")
     */
    public function index(Request $request, EntityManagerInterface $entity)
    {
        /*if($request->isXmlHttpRequest())
        {
            //Création du formulaire de filtrage
            $form = $this->createForm(FilterType::class);

            $offers = $this->getDoctrine()->getRepository(JobOffer::class)->findBy(array(
                'closing' => null,
                'offerType' => $entity->getRepository(OfferType::class)->findOneBy(array(
                    'name' => $request->getContent()
                ))
            ));

            return $this->render('user/listOffers.html.twig', array(
                'filterForm' => $form->createView(),
                'offers' => $offers,
            ));
        }*/

        $offers = $entity->getRepository(JobOffer::class)->findBy(array(
            'closing' => null,
        ));

        $postulations = $entity->getRepository(Postulation::class)->findBy(array(
            'user' => $this->getUser()
        ));

        return $this->render('user/listOffers.html.twig', array(
            'offers' => $offers,
            'postulations' => $postulations
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
        return $this->render('editlOffer.html.twig');
    }
}
