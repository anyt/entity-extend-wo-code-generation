<?php

namespace App\Doctrine;

use App\Doctrine\Entity\ExtendedEntityInterface;
use Doctrine\Common\Proxy\Proxy;

/**
 * The class is a copy of {@see \Doctrine\Persistence\Reflection\RuntimePublicReflectionProperty},
 * except for calling parent::getValue() it calls $object->get(), and instead of parent::setValue it calls $object->set
 */
class ReflectionVirtualProperty extends \ReflectionProperty
{
    /**
     * {@inheritDoc}
     * @param ExtendedEntityInterface $object
     *
     * Checks is the value actually exist before fetching it.
     * This is to avoid calling `__get` on the provided $object if it
     * is a {@see \Doctrine\Common\Proxy\Proxy}.
     */
    public function getValue($object = null)
    {
        if ($object instanceof Proxy && !$object->__isInitialized()) {
            $originalInitializer = $object->__getInitializer();
            $object->__setInitializer(null);
            $val = $object->get($this->name);
            $object->__setInitializer($originalInitializer);

            return $val;
        }

        return $object->get($this->name);
    }

    /**
     * {@inheritDoc}
     * @param ExtendedEntityInterface $object
     *
     * Avoids triggering lazy loading via `__set` if the provided object
     * is a {@see \Doctrine\Common\Proxy\Proxy}.
     *
     * @link https://bugs.php.net/bug.php?id=63463
     */
    public function setValue($object, $value = null)
    {
        if (!($object instanceof Proxy && !$object->__isInitialized())) {
            $object->set($this->name, $value);

            return;
        }

        $originalInitializer = $object->__getInitializer();
        $object->__setInitializer(null);
        $object->set($this->name, $value);
        $object->__setInitializer($originalInitializer);
    }
}
