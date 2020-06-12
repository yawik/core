<?php

/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright https://yawik.org/COPYRIGHT.php
 */

namespace Core\Factory\Controller;

use Core\Controller\AdminController;
use Core\Controller\AdminControllerEvent;
use Core\EventManager\EventManager;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * Creates new AdminController
 */
class AdminControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var EventManager $eventManager */
        $eventManager = $container->get('Core/AdminController/Events');
        $eventManager->setEventPrototype(new AdminControllerEvent());
        $ob = new AdminController($eventManager);
        return $ob;
    }
}
