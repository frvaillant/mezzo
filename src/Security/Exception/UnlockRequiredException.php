<?php

namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UnlockRequiredException extends AccessDeniedException
{
}

