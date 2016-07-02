<?php
/**
 * YAWIK
 *
 * @filesource
 * @license    MIT
 * @copyright  2013 - 2016 Cross Solution <http://cross-solution.de>
 */

/**  */
namespace Core\Controller\Plugin;

use Core\Listener\Events\CreatePaginatorEvent;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Mvc\Controller\PluginManager as ControllerManager;
use Zend\Paginator\Paginator;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Creates a paginator from the paginator service.
 *
 * Passing in GET (or POST) request parameters as creation options to the paginator manager.
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @author Anthonius Munthi <me@itstoni.com>
 */
class CreatePaginator extends AbstractPlugin
{
    const EVENT_CREATE_PAGINATOR = 'core.create_paginator';
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceManager;
    
    /**
     * @param ServiceLocatorInterface $serviceManager
     */
    public function __construct(ServiceLocatorInterface $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }
    
    /**
     * Creates a paginator from the paginator service.
     *
     * Uses query parameters from the request merged with $defaultParams as
     * creation options while retrieving the service.
     * Please note that a query parameter with the same name as a default parameter
     * overrides the default parameter.
     *
     *
     * @param string $paginatorName
     * @param array  $defaultParams
     * @param bool   $usePostParams if true, the POST parameters from the request are used.
     *
     * @return \Zend\Paginator\Paginator
     * @throws \InvalidArgumentException
     */
    public function __invoke($paginatorName, $defaultParams = array(), $usePostParams = false)
    {
        if (is_bool($defaultParams)) {
            $usePostParams = $defaultParams;
            $defaultParams = array();
        }

        if (!is_array($defaultParams) && !$defaultParams instanceof \Traversable) {
            throw new \InvalidArgumentException('$defaultParams must be an array or implement \Traversable');
        }

        /* @var $controller \Zend\Mvc\Controller\AbstractController
         * @var $paginators \Core\Paginator\PaginatorService
         * @var $request    \Zend\Http\Request
         */
        $controller = $this->getController();
        $paginators = $this->serviceManager->get('Core/PaginatorService');
        $request    = $controller->getRequest();
        $params     = $usePostParams
            ? $request->getPost()->toArray()
            : $request->getQuery()->toArray();

        // We allow \Traversable so we cannot simply merge.
        foreach ($defaultParams as $key => $val) {
            if (!isset($params[$key])) {
                $params[$key] = $val;
            }
        }

        /* try to create $paginator from event listener */
        /* @var \Core\EventManager\EventManager $events */
        /* @var \Zend\Paginator\Paginator $paginator */
        /* @var CreatePaginatorEvent $event */
        $events = $this->serviceManager->get('Core/CreatePaginator/Events');
        $event = $events->getEvent(CreatePaginatorEvent::EVENT_CREATE_PAGINATOR,$this,[
            'paginatorParams' => $params,
            'paginators' => $paginators,
            'paginatorName' => $paginatorName
        ]);
        $events->trigger($event);
        $paginator = $event->getPaginator();
        if(!$paginator instanceof Paginator){
            // no paginator created by listener, so let's create default paginator
            $paginator = $paginators->get($paginatorName,$params);
        }
        $paginator->setCurrentPageNumber(isset($params['page']) ? $params['page'] : 1)
                  ->setItemCountPerPage(isset($params['count']) ? $params['count'] : 10)
                  ->setPageRange(isset($params['range']) ? $params['range'] : 5);

        return $paginator;

    }
    
    /**
     * @param ControllerManager $controllerManager
     * @return CreatePaginator
     */
    public static function factory(ControllerManager $controllerManager)
    {
        return new static($controllerManager->getServiceLocator());
    }
}
