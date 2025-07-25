<?php

namespace App\Controller;

use App\Form\LoginType;
use App\Repository\SecurityCodeRepository;
use App\Security\SecurityParams;
use App\Security\UnlockCode\CodeTriesManager;
use App\Security\UnlockCode\UnlockCodeManager;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController extends AbstractController
{

    #[Route('/login/{isValid}', name: 'app_login')]
    public function login(
        RequestStack $requestStack,
        Request $request,
        UnlockCodeManager $unlockCodeManager,
        CodeTriesManager $codeTriesManager,
        bool $isValid = true,

    ): Response
    {
        $session = $requestStack->getSession();

        $form = $this->createForm(LoginType::class);
        $form->handleRequest($request);

        if($request->getSession()->get(SecurityParams::SESSION_KEY_NAME)) {
            return $this->redirectToRoute('app_home');
        }

        if(!$codeTriesManager->canSubmitCode()) {
            return $this->render('login.html.twig', [
                'form' => $form->createView(),
                'has_code' => $unlockCodeManager->hasCode(),
                'available_tries' => $codeTriesManager->getAvailableTries(),
                'remaining_time' => $codeTriesManager->getRemainingTimeToWait(),
                'locked' => true,
            ]);
        }

        if($form->isSubmitted() && $form->isValid()) {

            $codeTriesManager->manageTries();

            $submittedCode = $form->get('code')->getData();

            /**
             * Première utilisation, si pas de code, on le crée
             * TODO Créer un setup
             */
            if(!$unlockCodeManager->hasCode()) {
                $unlockCodeManager->setCode($submittedCode);
            }

            /**
             * On vérifie la validité du code
             */
            if($unlockCodeManager->isCodeValid($submittedCode)) {
                $session->set(SecurityParams::SESSION_KEY_NAME, true);
                $codeTriesManager->resetTries();
                return $this->redirectToRoute('app_cashbox');
            }

            return $this->redirectToRoute('app_login', ['isValid' => 0]);
        }

        return $this->render('login.html.twig', [
            'form' => $form->createView(),
            'has_code' => $unlockCodeManager->hasCode(),
            'is_valid' => $isValid,
            'available_tries' => $codeTriesManager->getAvailableTries()
        ]);

    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(RequestStack $requestStack)
    {

        $session = $requestStack->getSession();

        $session->remove(SecurityParams::SESSION_KEY_NAME);

        return $this->redirectToRoute('app_login');
    }


    #[Route('/unlock', name: 'app_unlock', methods: ['POST'])]
    public function unlock(RequestStack $requestStack, Request $request, UnlockCodeManager $unlockCodeManager)
    {
        $session = $requestStack->getSession();
        $form = $this->createForm(LoginType::class);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            $submittedCode = $form->get('code')->getData();
            if($unlockCodeManager->isCodeValid($submittedCode)) {
                $session->set(SecurityParams::SESSION_KEY_NAME, true);
                return new JsonResponse([], Response::HTTP_OK);
            }

        }

        return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
    }

    #[Route('/lock', name: 'app_lock', methods: ['POST'])]
    public function lock(RequestStack $requestStack)
    {
        $session = $requestStack->getSession();
        $session->remove(SecurityParams::SESSION_KEY_NAME);

        return new JsonResponse([], Response::HTTP_OK);
    }


    #[Route('/is-logged-in', name: 'app_is_logged_in', methods: ['GET'])]
    public function isLoggedIn(): JsonResponse
    {
        if($this->isGranted('IS_UNLOCKED')) {
            return new JsonResponse([], Response::HTTP_OK);
        }
        return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
    }

}
