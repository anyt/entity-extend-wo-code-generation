<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Doctrine\Entity\ExtendedEntityInterface;
use App\Doctrine\Entity\ExtendedEntityTrait;

/**
 * Defines the properties of the Tag entity to represent the post tags.
 *
 * See https://symfony.com/doc/current/doctrine.html#creating-an-entity-class
 *
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 */
class Tag implements \JsonSerializable, ExtendedEntityInterface
{
    use ExtendedEntityTrait;

    /**
     * @var int
     */
    private $id;

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): string
    {
        // This entity implements JsonSerializable (http://php.net/manual/en/class.jsonserializable.php)
        // so this method is used to customize its JSON representation when json_encode()
        // is called, for example in tags|json_encode (templates/form/fields.html.twig)

        return $this->name;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
