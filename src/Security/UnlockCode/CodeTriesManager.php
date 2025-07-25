<?php

namespace App\Security\UnlockCode;

use App\Security\SecurityParams;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CodeTriesManager
{

    private SessionInterface $session;
    public function __construct(private RequestStack $requestStack)
    {
        $this->session = $this->requestStack->getSession();
    }


    /**
     * @return bool
     */
    public function hasTriesAvailable(): bool
    {
        if(!$this->session->get(SecurityParams::CODE_TRIES)) {
            return true;
        }
        return $this->session->get(SecurityParams::CODE_TRIES) < SecurityParams::MAX_TRIES;
    }

    /**
     * @return bool
     */
    public function canRetryAfterBlocking(): bool
    {
        if(!$this->session->get(SecurityParams::LOCK_TRY_UNTIL)) {
            return true;
        }
        $now = new \DateTimeImmutable();
        $timestamp = $now->getTimestamp();

        return $timestamp > $this->session->get(SecurityParams::LOCK_TRY_UNTIL);
    }

    /**
     * @return void
     */
    public function manageTries(): void
    {

        $tries = $this->session->get(SecurityParams::CODE_TRIES) ?? 0;
        $tries++;
        $this->session->set(SecurityParams::CODE_TRIES, $tries);

        if($tries === SecurityParams::MAX_TRIES) {
            $blockUntil = new \DateTimeImmutable(SecurityParams::CODE_SUBMIT_BLOCKING_DELAY);
            $this->session->set(SecurityParams::LOCK_TRY_UNTIL, $blockUntil->getTimestamp());
        }
    }

    /**
     * @return void
     */
    public function resetTries(): void
    {
        $this->session->remove(SecurityParams::CODE_TRIES);
        $this->session->remove(SecurityParams::LOCK_TRY_UNTIL);
    }

    /**
     * @return int
     */
    public function getAvailableTries(): int
    {
        if(!$this->hasTriesAvailable()) {
            return 0;
        }
        return SecurityParams::MAX_TRIES - $this->session->get(SecurityParams::CODE_TRIES);
    }

    /**
     * @return bool
     */
    public function canSubmitCode(): bool
    {
        if ($this->hasTriesAvailable()) {
            return true;
        }
        if($this->canRetryAfterBlocking()) {
            $this->resetTries();
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getRemainingTimeToWait(): string
    {
        $now = new \DateTimeImmutable();
        $timestamp = $now->getTimestamp();

        $remainingTime = $this->session->get(SecurityParams::LOCK_TRY_UNTIL) - $timestamp;

        if ($remainingTime < 60) {
            return $remainingTime . 's';
        }

        $minutes = ceil($remainingTime / 60);
        return $minutes . ' min';
    }

}
