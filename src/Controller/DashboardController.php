<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Visit;
use App\Entity\Sale;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Security\Core\Security;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(Security $security, ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        $visitRepo = $doctrine->getRepository(Visit::class);
        $saleRepo = $doctrine->getRepository(Sale::class);

        if ($security->isGranted('ROLE_ADMIN')) {// All-users
            $perDayVisits = $visitRepo->countPerDay();
            $perDaySales = $saleRepo->countPerDay();
            $visitsTable = $visitRepo->findNewestLast5();
            $salesTable = $saleRepo->findNewestLast5();
        }
        else {// Current-user
            $perDayVisits = $visitRepo->countByUserPerDay($user);
            $perDaySales = $saleRepo->countByUserPerDay($user);
            $visitsTable = $visitRepo->findByUserNewestLast5($user);
            $salesTable = $saleRepo->findByUserNewestLast5($user);
        }

        $nDays = count($perDayVisits);

        // Determine Today-visits
        $todayVisits = $perDayVisits[$nDays - 1];

        // Determine Today-sales
        $todaySales = $perDaySales[$nDays - 1];

        // Determine Total-visits
        $totalVisits = 0;
        for ($i = 0; $i < $nDays; $i++) {
            $totalVisits += $perDayVisits[$i];
        }

        // Determine Total-sales
        $totalSales = 0;
        for ($i = 0; $i < $nDays; $i++) {
            $totalSales += $perDaySales[$i];
        }

        // Determine Average-visits
        $averageVisits = $totalVisits / $nDays;

        // Determine Average-sales
        $averageSales = $totalSales / $nDays;

        return $this->render('dashboard/index.html.twig', [
            'page' => 'index',
            'perDayVisits' => $perDayVisits,
            'visitsTable' => $visitsTable,
            'perDaySales' => $perDaySales,
            'salesTable' => $salesTable,
            'todayVisits' => $todayVisits,
            'todaySales' => $todaySales,
            'totalVisits' => $totalVisits,
            'totalSales' => $totalSales,
            'averageVisits' => $averageVisits,
            'averageSales' => $averageSales
        ]);
    }

    #[Route('/dashboard/visits/{page<\d+>}', name: 'app_dashboard_visits')]
    public function visits(Security $security, ManagerRegistry $doctrine, int $page = 1): Response
    {
        // Admin manages all Users
        if ($security->isGranted('ROLE_ADMIN')) {// All-users
            $queryBuilder = $doctrine->getRepository(Visit::class)->findNewestQueryBuilder();
        }
        else {// Current-user
            $queryBuilder = $doctrine->getRepository(Visit::class)->findByUserNewestQueryBuilder($this->getUser());
        }

        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pagerfanta->setMaxPerPage(100);
        $pagerfanta->setCurrentPage($page);

        return $this->render('dashboard/visits.html.twig', [
            'page' => 'visits',
            'visits' => $pagerfanta
        ]);
    }

    #[Route('/dashboard/links', name: 'app_dashboard_links')]
    public function links(HttpClientInterface $client): Response
    {
        $user = $this->getUser();
        $data = array();

        // Determine base url
        $baseUrl = $this->getParameter('appAddress') . '/' . $user->getSlug();

        // Get data from Thebrand API
        $sourceData = $client->request(
            'GET',
            $this->getParameter('thebrandApiBase') . '/get-links'
        );

        // Build Venues
        foreach ($sourceData->toArray() as $sourceVenue) {
            $venue = array();
            $categorySlug = $sourceVenue['events'][0]['category']['slug'];

            $venue['id']   = $sourceVenue['id'];
            $venue['name'] = $sourceVenue['name'];
            $venue['link'] = $baseUrl . '/' . $categorySlug . '/' . $sourceVenue['slug'];
            $venue['events'] = array();

            // Build Events
            foreach ($sourceVenue['events'] as $sourceEvent) {
                $event = array();

                $event['id']   = $sourceEvent['id'];
                $event['name'] = $sourceEvent['name'];
                $event['link'] = $baseUrl . '/' . $sourceEvent['category']['slug'] . '/' . $sourceVenue['slug'] . '/' . $sourceEvent['slug'];
                $event['dates'] = array();

                // Build Dates
                foreach ($sourceEvent['dates'] as $sourceDate) {
                    $date = array();
                    $timezone = new \DateTimeZone($sourceDate['date']['timezone']);
                    $datetime = \DateTimeImmutable::createFromFormat(
                        'Y-m-d H:i:s.u',
                        $sourceDate['date']['date'],
                        $timezone
                    );

                    $date['date'] = $datetime;
                    $date['link'] = $event['link'] . '/' . $datetime->format('Y-m-d');

                    array_push($event['dates'], $date);
                }

                array_push($venue['events'], $event);
            }

            array_push($data, $venue);
        }

        // Banned user has not links
        if ($user->isIsBanned()) {
            $data = array();
        }

        return $this->render('dashboard/links.html.twig', [
            'page' => 'links',
            'venues' => $data
        ]);
    }

    #[Route('/dashboard/sales/{page<\d+>}', name: 'app_dashboard_sales')]
    public function sales(Security $security, ManagerRegistry $doctrine, int $page = 1): Response
    {
        // Admin manages all Users
        if ($security->isGranted('ROLE_ADMIN')) {// All-users
            $queryBuilder = $doctrine->getRepository(Sale::class)->findNewestQueryBuilder();
        }
        else {// Current-user
            $queryBuilder = $doctrine->getRepository(Sale::class)->findByUserNewestQueryBuilder($this->getUser());
        }

        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pagerfanta->setMaxPerPage(50);
        $pagerfanta->setCurrentPage($page);

        return $this->render('dashboard/sales.html.twig', [
            'page' => 'sales',
            'sales' => $pagerfanta
        ]);
    }
}
