<?php

namespace App\Doctrine;

use Doctrine\Persistence\Mapping\RuntimeReflectionService;

class VirtualPropertyRuntimeReflectionService extends RuntimeReflectionService
{
    private \stdClass $propertyDonor;

    public function __construct()
    {
        parent::__construct();
        $this->propertyDonor = new \stdClass();
    }

    /**
     * {@inheritDoc}
     */
    public function getAccessibleProperty($class, $property)
    {
        if (property_exists($class, $property)) {
            return parent::getAccessibleProperty($class, $property);
        }
        $this->propertyDonor->{$property} = null;

        return new ReflectionVirtualProperty($this->propertyDonor, $property);
    }
}
