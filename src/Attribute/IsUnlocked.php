<?php

namespace App\Attribute;

use \Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class IsUnlocked
{
    public function __construct(public ?string $subject = null) {}
}

