<?php

namespace App\Controller;

use App\Form\FilterType;
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
        $form = $this->createForm(FilterType::class);

        if($request->isXmlHttpRequest())
        {
            //Récupération des offres de la catégories demandées et qui ne sont pas liées à l'utilisateur actuelle
            $queryOffers = $entity->createQueryBuilder();
            $queryOffers->select('offers')
                ->from('App:JobOffer', 'offers')
                ->join('offers.offerType', 'type')
                ->join('offers.city', 'city')
                ->join('offers.category', 'category')
                ->join('offers.offerType', 'offerType')
                ->join('offers.user', 'user')
                ->addSelect('city.name AS cityName')
                ->addSelect('city.npa AS cityNPA')
                ->addSelect('category.title AS categoryTitle')
                ->addSelect('offerType.name AS typeOffer')
                ->where('type.name = :type')
                ->andWhere('offers.isActive = true')
                ->orderBy('offers.publicationDate', 'DESC')
                ->setParameters(array(
                    'type' => $request->query->get('typeOffer'),
                ));

            return new JsonResponse(array(
                'offers' => $queryOffers->getQuery()->getArrayResult(),
            ));
        }

        return $this->render('user/listOffers.html.twig', array(
            'filterForm' => $form->createView()
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

    /**
     * @Route("filtrer", name="filterList")
     */
    public function filterList(Request $request, EntityManagerInterface $entity)
    {
        if($request->isXmlHttpRequest())
        {
            $jobOffers = $entity->createQueryBuilder();
            $jobOffers->select('jobOffers')
                ->from('App:JobOffer', 'jobOffers');

            /*
             * Ajout des paramètres en fonction des choix du filtre
             */
            if($request->get('idCity') !== "")
            {
                $jobOffers->andWhere('jobOffers.city = :idCity')
                    ->setParameter('idCity', $request->get('idCity'));
            }

            if($request->get('idCategory') !== "")
            {
                $jobOffers->andWhere('jobOffers.category = :idCategory')
                    ->setParameter('idCategory', $request->get('idCategory'));
            }

            if($request->get('date') !== "")
            {
                $jobOffers->andWhere('jobOffers.publicationDate >= :date')
                    ->setParameter('date', $request->get('date'));
            }

            $jobOffers->join('jobOffers.offerType', 'typeOffer')
                ->join('jobOffers.city', 'offerCity')
                ->join('jobOffers.category', 'offerCategory')
                ->addSelect('offerCity.name AS cityName')
                ->addSelect('offerCity.npa AS cityNPA')
                ->addSelect('offerCategory.title as category')
                ->andWhere('typeOffer.name = :typeOffer')
                ->andWhere('jobOffers.isActive = TRUE')
                ->setParameter('typeOffer', $request->get('typeOffer'))
                ->orderBy('jobOffers.publicationDate', 'DESC');

            return new JsonResponse(array(
                'data' => $jobOffers->getQuery()->getArrayResult()
            ));
        }
    }
}
