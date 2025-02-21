<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

class AdminUserController extends AbstractController
{
    #[Route('/dashboard/users/{page<\d+>}', name: 'app_dashboard_users')]
    public function users(ManagerRegistry $doctrine, int $page = 1): Response
    {
        $queryBuilder = $doctrine->getRepository(User::class)->findNewestQueryBuilder();

        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pagerfanta->setMaxPerPage(25);
        $pagerfanta->setCurrentPage($page);

        return $this->render('dashboard/users.html.twig', [
            'page' => 'users',
            'users' => $pagerfanta
        ]);
    }

    #[Route('/dashboard/users/view/{userId<\d+>}', name: 'app_dashboard_users_view')]
    public function viewUser(ManagerRegistry $doctrine, int $userId): Response
    {
        $viewUser = $doctrine->getRepository(User::class)->find($userId);

        if (!$viewUser) {
            throw $this->createNotFoundException('Invalid Id');
        }

        return $this->render('dashboard/user.html.twig', [
            'page' => 'users',
            'viewUser' => $viewUser
        ]);
    }

    #[Route('/dashboard/users/ban/{userId<\d+>}', name: 'app_dashboard_users_ban')]
    public function banUser(ManagerRegistry $doctrine, int $userId): Response
    {
        $banUser = $doctrine->getRepository(User::class)->find($userId);
        $adminUser = $this->getUser();

        if (!$banUser) {
            throw $this->createNotFoundException('Invalid Id');
        }

        $banUser->setIsBanned(true);
        $banUser->setBannedBy($adminUser);
        $banUser->setBannedDate(new \DateTimeImmutable());

        // Persist
        $doctrine->getRepository(User::class)->save($banUser, true);

        // Redirect
        return $this->redirectToRoute('app_dashboard_users_view', ['userId' => $userId]);
    }

    #[Route('/dashboard/users/unban/{userId<\d+>}', name: 'app_dashboard_users_unban')]
    public function unbanUser(ManagerRegistry $doctrine, int $userId): Response
    {
        $banUser = $doctrine->getRepository(User::class)->find($userId);
        $adminUser = $this->getUser();

        if (!$banUser) {
            throw $this->createNotFoundException('Invalid Id');
        }

        $banUser->setIsBanned(false);
        $banUser->setBannedBy(null);
        $banUser->setBannedDate(null);

        // Persist
        $doctrine->getRepository(User::class)->save($banUser, true);

        // Redirect
        return $this->redirectToRoute('app_dashboard_users_view', ['userId' => $userId]);
    }
}
