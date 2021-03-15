<?php

namespace App\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\Mapping\AbstractClassMetadataFactory;

/**
 * We can decorate the original entity manager instead of extending it.
 */
class ExtendedEntityManager extends EntityManager
{
    protected function __construct(Connection $conn, Configuration $config, EventManager $eventManager)
    {
        parent::__construct($conn, $config, $eventManager);
        $this->replaceReflectionService();
    }

    public static function create($connection, Configuration $config, ?EventManager $eventManager = null)
    {
        // start copy paste from the parent entity manager
        if (!$config->getMetadataDriverImpl()) {
            throw ORMException::missingMappingDriverImpl();
        }

        $connection = static::createConnection($connection, $config, $eventManager);

        // end copy paste from the parent entity manager

        return new ExtendedEntityManager($connection, $config, $connection->getEventManager());
    }

    private function replaceReflectionService(): void
    {
        /** @var AbstractClassMetadataFactory $cmf */
        $cmf = $this->getMetadataFactory();
        $reflectionService = new VirtualPropertyRuntimeReflectionService();
        $cmf->setReflectionService($reflectionService);
    }
}
