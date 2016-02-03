<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 * @author    weitz@cross-solution.de
 */

namespace Core\Entity\Hydrator;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EntityHydratorFactory implements FactoryInterface
{
    protected $hydrator;

    /**
     * Create the Json Entity Hydrator
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return JsonEntityHydrator
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $this->hydrator = $this->getEntityHydrator();
        $this->prepareHydrator();
        return $this->hydrator;
    }

    protected function prepareHydrator()
    {
    }

    protected function getEntityHydrator()
    {
        return new EntityHydrator();
    }
}
