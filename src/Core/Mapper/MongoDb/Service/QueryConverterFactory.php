<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 Cross Solution (http://cross-solution.de)
 * @license   GPLv3
 */

/** Services of MongoDb mappers */
namespace Core\Mapper\MongoDb\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Core\Mapper\MongoDb\QueryConverter;

/**
 *  Factory 
 */
class QueryConverterFactory implements FactoryInterface
{

    /*
     * @param \Zend\ServiceManager\ServiceLocatorinterface $serviceLocator
     * @return \MongoDB
     * @see \Zend\ServiceManager\FactoryInterface::createService()
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $converter = new QueryConverter();
        $converter->setCriterionConverterPluginManager(
            $serviceLocator->get('query_criterion_converter_manager')
        );
        return $converter;
    }
    
    
}


    