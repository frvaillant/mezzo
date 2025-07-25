<?php

namespace App\Tests\Security\UnlockCode;

use App\Security\SecurityParams;
use App\Security\UnlockCode\CodeTriesManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CodeTriesManagerTest extends TestCase
{
    private SessionInterface $session;
    private CodeTriesManager $manager;

    protected function setUp(): void
    {
        $this->session = $this->createMock(SessionInterface::class);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')->willReturn($this->session);

        $this->manager = new CodeTriesManager($requestStack);
    }

    public function testHasTriesAvailableWhenNoTriesStored(): void
    {
        $this->session->method('get')
            ->with(SecurityParams::CODE_TRIES)
            ->willReturn(null);

        $this->assertTrue($this->manager->hasTriesAvailable());
    }

    public function testHasTriesAvailableWhenBelowLimit(): void
    {
        $this->session->method('get')
            ->with(SecurityParams::CODE_TRIES)
            ->willReturn(2);

        $this->assertTrue($this->manager->hasTriesAvailable());
    }

    public function testHasTriesAvailableWhenAtLimit(): void
    {
        $this->session->method('get')
            ->with(SecurityParams::CODE_TRIES)
            ->willReturn(SecurityParams::MAX_TRIES);

        $this->assertFalse($this->manager->hasTriesAvailable());
    }

    public function testCanRetryAfterBlockingWhenNoLock(): void
    {
        $this->session->method('get')
            ->with(SecurityParams::LOCK_TRY_UNTIL)
            ->willReturn(null);

        $this->assertTrue($this->manager->canRetryAfterBlocking());
    }

    public function testCanRetryAfterBlockingWhenTimeExpired(): void
    {
        $this->session->method('get')
            ->with(SecurityParams::LOCK_TRY_UNTIL)
            ->willReturn(time() - 10);

        $this->assertTrue($this->manager->canRetryAfterBlocking());
    }

    public function testCanRetryAfterBlockingWhenTimeNotExpired(): void
    {
        $this->session->method('get')
            ->with(SecurityParams::LOCK_TRY_UNTIL)
            ->willReturn(time() + 100);

        $this->assertFalse($this->manager->canRetryAfterBlocking());
    }

    public function testGetAvailableTriesReturnsCorrectCount(): void
    {
        $this->session->method('get')
            ->with(SecurityParams::CODE_TRIES)
            ->willReturn(2);

        $this->assertEquals(
            SecurityParams::MAX_TRIES - 2,
            $this->manager->getAvailableTries()
        );
    }

    public function testGetAvailableTriesWhenBlocked(): void
    {
        $this->session->method('get')->willReturnCallback(function ($key) {
            return match ($key) {
                SecurityParams::CODE_TRIES => SecurityParams::MAX_TRIES,
                SecurityParams::LOCK_TRY_UNTIL => time() + 100,
                default => null,
            };
        });

        $this->assertEquals(0, $this->manager->getAvailableTries());
    }

    public function testCanSubmitCodeWhenTriesAvailable(): void
    {
        $this->session->method('get')
            ->with(SecurityParams::CODE_TRIES)
            ->willReturn(1);

        $this->assertTrue($this->manager->canSubmitCode());
    }

    public function testGetRemainingTimeToWaitReturnsCorrectFormat(): void
    {
        $futureTimestamp = time() + 75;

        $this->session->method('get')
            ->with(SecurityParams::LOCK_TRY_UNTIL)
            ->willReturn($futureTimestamp);

        $result = $this->manager->getRemainingTimeToWait();

        $this->assertEquals('2 min', $result);
    }

    public function testGetRemainingTimeToWaitReturnsSeconds(): void
    {
        $futureTimestamp = time() + 30;

        $this->session->method('get')
            ->with(SecurityParams::LOCK_TRY_UNTIL)
            ->willReturn($futureTimestamp);

        $result = $this->manager->getRemainingTimeToWait();

        $this->assertMatchesRegularExpression('/^\d{1,2}s$/', $result);
    }
}
