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
     */
    public function index()
    {
        return $this->render('index.html.twig');
    }

    /**
     * @Route("/recherche-emploi", name="searchJob")
     */
    public function listSearchJob(EntityManagerInterface $entity)
    {
        $form = $this->createForm(FilterType::class);

        $querySelectAll = $entity->createQueryBuilder();
        $querySelectAll->select('jobOffer')
            ->from('App:JobOffer', 'jobOffer')
            ->join('jobOffer.offerType', 'typeOffer')
            ->where('typeOffer.name = :name')
            ->andWhere('jobOffer.isActive = true')
            ->andWhere('jobOffer.closing IS NULL')
            ->setParameter('name', 'searchJob');

        $listOffer = $querySelectAll->getQuery()->getResult();

        return $this->render('user/listOffers.html.twig', array(
            'filterForm' => $form->createView(),
            'offers' => $listOffer
        ));
    }

    /**
     * @Route("/offre-emploi", name="searchOffers")
     */
    public function listOfferJob(EntityManagerInterface $entity)
    {
        $form = $this->createForm(FilterType::class);

        $querySelectAll = $entity->createQueryBuilder();
        $querySelectAll->select('jobOffer')
            ->from('App:JobOffer', 'jobOffer')
            ->join('jobOffer.offerType', 'typeOffer')
            ->where('typeOffer.name = :name')
            ->andWhere('jobOffer.isActive = true')
            ->andWhere('jobOffer.closing IS NULL')
            ->setParameter('name', 'offerJob');

        $listOffer = $querySelectAll->getQuery()->getResult();

        return $this->render('user/listOffers.html.twig', array(
            'filterForm' => $form->createView(),
            'offers' => $listOffer
        ));
    }

    /**
     * @Route("cheque-emploi", name="infoChequeEmploi")
     */
    public function viewChequeEmploi()
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

            if($request->get('district') !== "")
            {
                $jobOffers->andWhere('offerCity.district = :idDistrict')
                    ->setParameter('idDistrict', $request->get('district'));
            }


            $jobOffers->join('jobOffers.offerType', 'typeOffer')
                ->join('jobOffers.city', 'offerCity')
                ->join('jobOffers.category', 'offerCategory')
                ->join('offerCity.district', 'cityDistrict')
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
