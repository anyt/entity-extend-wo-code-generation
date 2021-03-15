<?php

namespace App\Doctrine\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\String\UnicodeString;

/**
 * Todo handle get and set of non existing properties.
 */
trait ExtendedEntityTrait
{
    protected array $tableColumns = [];

    public function get(string $name): mixed
    {
        return $this->tableColumns[$name] ?? null;
    }

    public function set(string $name, $value): static
    {
        if (array_key_exists($name, $this->tableColumns) && $this->tableColumns[$name] instanceof Collection) {
            return $this->setCollectionValue($name, $value);
        }
        // a workaround because we don't have value type checks (converters)
        if ($value instanceof UnicodeString) {
            $value = $value->__toString();
        }

        $this->tableColumns[$name] = $value;

        return $this;
    }

    public function __get(string $name)
    {
        return $this->get($name);
    }

    public function __set(string $name, $value)
    {
        $this->set($name, $value);
    }

    public function __call(string $name, array $arguments)
    {
        if (str_starts_with($name, 'get')) {
            $name = lcfirst(substr($name, 3));

            return $this->get($name);
        }
        if (str_starts_with($name, 'set')) {
            $name = lcfirst(substr($name, 3));
            $this->set($name, reset($arguments));

            return $this;
        }
        // todo consider to use `$this->inflector->singularize($camelized)`
        if (str_starts_with($name, 'add')) {
            $name = lcfirst(substr($name, 3));
            $this->addToCollection($name, $arguments);

            return $this;
        }
        if (str_starts_with($name, 'remove')) {
            $name = lcfirst(substr($name, 6));
            $this->removeFromCollection($name, reset($arguments));

            return $this;
        }

        if (array_key_exists($name, $this->tableColumns)) {
            return $this->get($name);
        }

        throw new \RuntimeException(sprintf("Method %s doesn't exist in %s", $name, __CLASS__));
    }

    private function setCollectionValue(string $name, $value): static
    {
        if ($value instanceof PersistentCollection) {
            $this->tableColumns[$name] = $value;

            return $this;
        }

        // todo make sure we need it by submitting the symfony form and checking the query count
        $previousValue = &$this->tableColumns[$name];
        if (\is_object($value)) {
            $value = iterator_to_array($value);
        }

        if (!\is_array($value)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Field "%s" accepts only array or an instance of \Doctrine\Common\Collections\Collection',
                    $name
                )
            );
        }

        foreach ($previousValue as $item) {
            if (!\in_array($item, $value, true)) {
                $previousValue->removeElement($item);
            }
        }

        $this->addToCollection($name, $value);

        return $this;
    }

    private function addToCollection(string $name, $items): void
    {
        if (!array_key_exists($name, $this->tableColumns)) {
            $this->tableColumns[$name] = new ArrayCollection();
        }

        foreach ($items as $item) {
            // to cache
            $shortClassName = $this->shortClassName();
            $inverseSetter = 'set'.$shortClassName;
            if (method_exists($item, $inverseSetter)) {
                $item->{$inverseSetter}($this);
            }
            $inverseField = lcfirst($shortClassName);
            if ($item instanceof ExtendedEntityInterface) {
                $item->set($inverseField, $this);
            }
            // to cache

            if (!$this->tableColumns[$name]->contains($item)) {
                $this->tableColumns[$name]->add($item);
            }
        }
    }

    private function removeFromCollection(string $name, $value): void
    {
        if (!array_key_exists($name, $this->tableColumns)) {
            return;
        }
        if (!$this->tableColumns[$name]->contains($value)) {
            $this->tableColumns[$name]->removeElement($value);
        }
    }

    private function shortClassName(): string
    {
        return substr(strrchr(__CLASS__, "\\"), 1);
    }
}
