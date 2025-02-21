<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Security\EmailNotVerifiedAuthenticationException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Authenticator\Passport\UserPassportInterface;
use App\Entity\User;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class CheckVerifiedUserSubscriber implements EventSubscriberInterface
{
    private RouterInterface $router;
    private EmailVerifier $emailVerifier;
    private $translator;

    public function __construct(RouterInterface $router, EmailVerifier $emailVerifier, TranslatorInterface $translator)
    {
        $this->router = $router;
        $this->emailVerifier = $emailVerifier;
        $this->translator = $translator;
    }

    public function onCheckPassport(CheckPassportEvent $event)
    {
        $passport = $event->getPassport();

        if (!$passport instanceof UserPassportInterface) {
            throw new \Exception('Unexpected passport type');
        }

        $user = $passport->getUser();

        if (!$user instanceof User) {
            throw new \Exception('Unexpected user type');
        }

        if ($user->isVerified()) {
            return;
        }

        // generate a signed url and email it to the user
        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address('amb@thebrand.com', 'Affidash'))
                ->to($user->getEmail())
                ->subject($this->translator->trans('preuser.verify.email.subject'))
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );
        
        throw new EmailNotVerifiedAuthenticationException();
    }

    public function onLoginFailure(LoginFailureEvent $event)
    {
        if (!$event->getException() instanceof EmailNotVerifiedAuthenticationException) {
            return;
        }

        $response = new RedirectResponse(
            $this->router->generate('app_verify_check')
        );
        $event->setResponse($response);
    }

    public static function getSubscribedEvents()
    {
        return [
            CheckPassportEvent::class => ['onCheckPassport', -10],
            LoginFailureEvent::class => 'onLoginFailure'
        ];
    }
}
