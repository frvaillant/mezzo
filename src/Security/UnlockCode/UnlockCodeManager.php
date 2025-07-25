<?php

namespace App\Security\UnlockCode;

use App\Entity\SecurityCode;
use App\Repository\SecurityCodeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class UnlockCodeManager
{

    private PasswordHasherInterface $hasher;
    public function __construct(
        private readonly SecurityCodeRepository $securityCodeRepository,
        private readonly EntityManagerInterface $manager,
    )
    {
        $factory = new PasswordHasherFactory([
            'sodium' => ['algorithm' => 'sodium'],
        ]);
        $this->hasher = $factory->getPasswordHasher('sodium');
    }

    /**
     * @return bool
     */
    public function hasCode(): bool
    {
        return $this->securityCodeRepository->count() > 0;
    }

    /**
     * @param string $code
     * @return void
     */
    public function setCode(string $code): void
    {
        $securityCode = new SecurityCode();
        $securityCode->setAccessCode($this->hasher->hash($code));
        $this->manager->persist($securityCode);
        $this->manager->flush();

    }

    /**
     * @param string $code
     * @return bool
     */
    public function isCodeValid(string $code): bool
    {
        /** @var SecurityCode $unlockCode */
        $unlockCode = $this->securityCodeRepository->getCode();
        return $this->hasher->verify($unlockCode->getAccessCode(), $code);
    }

}
