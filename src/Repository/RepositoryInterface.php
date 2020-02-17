<?php

namespace Core\Repository;

use Core\Entity\EntityInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

interface RepositoryInterface
{
    public function init(ServiceLocatorInterface $serviceLocator);
    public function setEntityPrototype(EntityInterface $entity);
    public function create(array $data = null, $persist = false);
}
