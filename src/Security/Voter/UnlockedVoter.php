<?php

namespace App\Security\Voter;

use App\Security\Exception\UnlockRequiredException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\HttpFoundation\RequestStack;

class UnlockedVoter extends Voter
{
    public function __construct(private RequestStack $requestStack) {}

    protected function supports(string $attribute, $subject): bool
    {
        return $attribute === 'IS_UNLOCKED';
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $session = $this->requestStack->getSession();

        if ($session?->get('app_unlocked') === true) {
            return true;
        }

        return false;
    }
}

