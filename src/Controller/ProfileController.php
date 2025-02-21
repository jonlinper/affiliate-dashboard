<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\User;
use App\Form\EditProfileFormType;
use App\Form\PasswordProfileFormType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProfileController extends AbstractController
{
    #[Route('/dashboard/profile/locale/{locale}', name: 'app_profile_locale')]
    public function locale(Request $request, ManagerRegistry $doctrine, string $locale): Response
    {
        $user = $this->getUser();

        // Get value from url
        if ($locale == 'es') {
            $value = 'es';
        }
        else {
            $value = $this->getParameter('kernel.default_locale');
        }

        // Persist
        $user->setLocale($value);
        $doctrine->getRepository(User::class)->save($user, true);

        // Update Session
        $request->getSession()->set('_locale', $value);

        // Redirect
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/dashboard/profile/edit', name: 'app_profile_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(EditProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Persist
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('profile.edit.success'));

            return $this->redirectToRoute('app_profile_edit');
        }

        return $this->render('profile/edit.html.twig', [
            'page' => '',
            'editForm' => $form->createView()
        ]);
    }

    #[Route('/dashboard/profile/password', name: 'app_profile_password')]
    public function password(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, TranslatorInterface $translator): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(PasswordProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPass = $form->get('plainPassword')->getData();

            // Encode New-password
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $newPass)
            );

            // Persist
            $entityManager->persist($user);
            $entityManager->flush();

            // TODO: Send email

            $this->addFlash('success', $translator->trans('profile.edit.success'));

            return $this->redirectToRoute('app_profile_edit');
        }

        return $this->render('profile/password.html.twig', [
            'page' => '',
            'form' => $form->createView()
        ]);
    }
}
