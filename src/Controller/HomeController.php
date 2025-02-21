<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{
    /**
     * @Route({
     *     "en": "/",
     *     "es": "/es"
     * }, name="app_home")
     */
    public function index(Security $security, Request $request): Response
    {
        // Redirect if already logged-in
        if ($security->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('app_dashboard');
        }

        // Determine Template based on Locale
        if ($request->getLocale() == 'es') {
            $template = 'home/index.es.html.twig';
        }
        else {
            $template = 'home/index.html.twig';
        }

        return $this->render($template);
    }
}
