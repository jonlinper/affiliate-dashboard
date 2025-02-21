<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Visit;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\AbstractDeviceParser;

class VisitController extends AbstractController
{
    private function determineIp(Request $request): string
    {
        $a = $request->server->get('HTTP_CLIENT_IP');
        $b = $request->server->get('HTTP_X_FORWARDED_FOR');
        $c = $request->server->get('REMOTE_ADDR');

        if ($a) {
            return $a;
        }
        elseif ($b) {
            return $b;
        }
        else {
            return $c;
        }
    }

    /**
     * @Route("/{slug}/{redirectPath}", name="app_visit", requirements={"slug"="[a-z0-9]+","redirectPath"=".+"})
     */
    public function index(Request $request, ManagerRegistry $doctrine, string $slug, string $redirectPath): Response
    {
        // Determine client IP
        $ip = $this->determineIp($request);

        // Determine Device
        $dd = new DeviceDetector($request->headers->get('User-Agent'), ClientHints::factory($_SERVER));
        $dd->parse();

        // Build Visit-data
        $visitData = array(
            'client' => $dd->getClient(),
            'os' => $dd->getOs(),
            'device' => $dd->getDeviceName(),
            'brand' => $dd->getBrandName(),
            'model' => $dd->getModel(),
            'ip' => $ip,
            'querystring' => $request->server->get('QUERY_STRING')
        );

        // Determine Owner
        $owner = $doctrine->getRepository(User::class)->findOneBy(['slug' => $slug]);
        if (!$owner) {
            throw $this->createNotFoundException('Invalid Slug');
        }

        // Determine Redirect-to
        $redirectTo = $this->getParameter('redirectBase') . '/' . $redirectPath;

        // Persist this navigation
        if (!$owner->isIsBanned()) {
            $visit = new Visit();
            $visit->setVisitData($visitData);
            $visit->setRedirectTo($redirectTo);
            $visit->setSource($request->query->get('source'));
            $visit->setMedium($request->query->get('medium'));
            $visit->setReferer($request->headers->get('Referer'));
            $visit->setUser($owner);
            $doctrine->getRepository(Visit::class)->storeNavigation($visit);
        }

        // Redirect
        if ($owner->isIsBanned()) {
            return $this->redirect($redirectTo);
        }
        else {
            return $this->redirect($redirectTo . '?ambref=' . $visit->getId());
        }
    }
}
