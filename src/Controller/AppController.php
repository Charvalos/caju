<?php

namespace App\Controller;

use App\Entity\OfferType;
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
        return $this->render('utils/chequeEmploi.html.twig');
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
            if(strpos($request->headers->get('referer'), 'offre-emploi'))
                $idOfferType = $entity->getRepository(OfferType::class)->findOneBy(array(
                    'name' => 'offerJob'
                ));
            else
                $idOfferType = $entity->getRepository(OfferType::class)->findOneBy(array(
                    'name' => 'searchJob'
                ));

            $jobOffers = $entity->createQueryBuilder();
            $jobOffers->select('jobOffers')
                ->from('App:JobOffer', 'jobOffers')
                ->join('jobOffers.city', 'city')
                ->join('jobOffers.category', 'category')
                ->addSelect('city.npa AS cityNPA')
                ->addSelect('city.name AS cityName')
                ->addSelect('category.title AS categoryTitle');

            //Ajout des paramètres de filtrage si indiqués
            if($request->get('idCity') !== "")
                $jobOffers->where('jobOffers.city = :city')
                    ->setParameter('city', $request->get('idCity'));

            if($request->get('idCategory') !== "")
                $jobOffers->andWhere('jobOffers.category = :category')
                    ->setParameter('category', $request->get('idCategory'));

            if($request->get('district') !== "")
                $jobOffers->andWhere('city.district = :district')
                    ->setParameter('district', $request->get('district'));

            $jobOffers->andWhere('jobOffers.offerType = :offerType')
                ->setParameter('offerType', $idOfferType);

            $dateMin = $entity->createQueryBuilder();
            $dateMin->select('jobOffers, MIN(jobOffers.publicationDate)')
                ->from('App:JobOffer', 'jobOffers');

            $jobOffers->andWhere('jobOffers.publicationDate >= :date')
                ->andWhere('jobOffers.isActive = true')
                ->setParameter('date', $dateMin->getQuery()->getResult());

            return new JsonResponse(array(
                'data' => $jobOffers->getQuery()->getArrayResult()
            ));
        }
    }
}
