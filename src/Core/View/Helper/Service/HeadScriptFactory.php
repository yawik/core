<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 Cross Solution (http://cross-solution.de)
 * @license   GPLv3
 */

namespace Core\View\Helper\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\HeadScript;

/**
 * 
 */
class HeadScriptFactory implements FactoryInterface 
{

    /**
     * Creates an instance of \Zend\View\Helper\Headscript
     * 
     * - injects the MvcEvent instance
     * 
     * @param ServiceLocatorInterface $serviceLocator
     * @return HeadScript
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $helper   = new HeadScript();
        $services = $serviceLocator->getServiceLocator();
        $config   = $services->get('Config');
        
        if (!isset($config['view_helper_config']['headscript'])) {
            return $helper;
        }
        
        $config     = $config['view_helper_config']['headscript'];
        
        $routeMatch = $services->get('Application')->getMvcEvent()->getRouteMatch();
        $routeName  = $routeMatch ? $routeMatch->getMatchedRouteName() : '';
        $basepath = $serviceLocator->get('basepath');
        
        foreach ($config as $routeStart => $specs) {
            if (!is_int($routeStart)) {
                if (0 !== strpos($routeName, $routeStart)) {
                    continue;
                }
            } else {
                $specs = array($specs);
            }
            
            foreach ($specs as $spec) {
                if (is_string($spec)) {
                    $helper->appendFile($basepath($spec));
                    continue;
                }
                
                if ($helper::SCRIPT != $spec[0]) {
                    $spec[1] = $basepath($spec[1]);
                }
                
                call_user_func_array($helper, $spec);
            }
        }
         
        return $helper;
        
    }
    
}