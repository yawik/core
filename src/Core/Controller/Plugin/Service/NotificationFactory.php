<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

/** NotificationFactory.php */
namespace Core\Controller\Plugin\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Core\Controller\Plugin\Notification;

class NotificationFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var \Zend\Mvc\Controller\PluginManager $serviceLocator */
        $services = $serviceLocator->getServiceLocator();
        $flashMessenger = $serviceLocator->get('FlashMessenger');
        $notificationListener = $serviceLocator->getServiceLocator()->get('Core/Listener/Notification');
        $translator = $services->get('translator');

        $notification   = new Notification($flashMessenger);
        $notification->setListener($notificationListener);
        $notification->setTranslator($translator);

        return $notification;
    }
}
