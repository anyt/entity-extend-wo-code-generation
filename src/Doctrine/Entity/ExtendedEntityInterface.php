<?php

namespace App\Doctrine\Entity;

interface ExtendedEntityInterface
{
    public function set(string $name, $value): static;

    public function get(string $name): mixed;
}
