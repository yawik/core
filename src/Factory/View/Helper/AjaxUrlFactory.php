<?php
/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright https://yawik.org/COPYRIGHT.php
 */

/** */
namespace Core\Factory\View\Helper;

use Core\View\Helper\AjaxUrl;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for \Core\View\Helper\AjaxUrl
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @since 0,29
 */
class AjaxUrlFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array              $options
     *
     * @return AjaxUrl
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $request = $container->get('Request');
        $basepath = $request->getBasePath();
        $helper = new AjaxUrl($basepath);

        return $helper;
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface|AbstractPluginManager $serviceLocator
     *
     * @return AjaxUrl
     * @deprecated use __invoke()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, AjaxUrl::class);
    }
}