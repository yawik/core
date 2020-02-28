<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

/** RepositoryServiceFactory.php */
namespace Core\Repository;

use Core\Repository\DoctrineMongoODM\PersistenceListener;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class RepositoryServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $dm      = $container->get('Core/DocumentManager');
        $service = new RepositoryService($dm);
        
        /* Attach persistence listener */
        $application = $container->get('Application');
        $events      = $application->getEventManager();
        $persistenceListener = new PersistenceListener($service);
        $persistenceListener->attach($events);
        
        return $service;
    }
}
