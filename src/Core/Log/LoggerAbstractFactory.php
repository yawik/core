<?php
/**
 * YAWIK
 *
 * (this file is taken from ZF 2.2)
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

/** LoggerAbstractFactory.php */
namespace Core\Log;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Log\Logger;

/**
 * Logger abstract service factory.
 *
 * Allow to configure multiple loggers for application.
 */
class LoggerAbstractFactory implements AbstractFactoryInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Configuration key holding logger configuration
     *
     * @var string
     */
    protected $configKey = 'log';

    /**
     * @param  ServiceLocatorInterface $services
     * @param  string                  $name
     * @param  string                  $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        $config = $this->getConfig($services);
        if (empty($config)) {
            return false;
        }

        return isset($config[$requestedName]);
    }

    /**
     * @param  ServiceLocatorInterface $services
     * @param  string                  $name
     * @param  string                  $requestedName
     * @return Logger
     */
    public function createServiceWithName(ServiceLocatorInterface $services, $name, $requestedName)
    {
        $config  = $this->getConfig($services);
        $config  = $config[$requestedName];
        if (is_string($config) || isset($config['service'])) {
            $serviceName = is_string($config) ? $config : $config['service'];
            return $services->get($serviceName);
        }
        $this->processConfig($config, $services);
        return new Logger($config);
    }

    /**
     * Retrieve configuration for loggers, if any
     *
     * @param  ServiceLocatorInterface $services
     * @return array
     */
    protected function getConfig(ServiceLocatorInterface $services)
    {
        if ($this->config !== null) {
            return $this->config;
        }

        if (!$services->has('Config')) {
            $this->config = array();
            return $this->config;
        }

        $config = $services->get('Config');
        if (!isset($config[$this->configKey])) {
            $this->config = array();
            return $this->config;
        }

        $this->config = $config[$this->configKey];
        return $this->config;
    }

    protected function processConfig(&$config, ServiceLocatorInterface $services)
    {
        if (!isset($config['writer_plugin_manager'])) {
            $config['writer_plugin_manager'] = $services->get('LogWriterManager');
        }
        if (!isset($config['processor_plugin_manager'])) {
            $config['processor_plugin_manager'] = $services->get('LogProcessorManager');
        }

        if (!isset($config['writers'])) {
            return;
        }

        foreach ($config['writers'] as $index => $writerConfig) {
            if (!isset($writerConfig['options']['db'])
            || !is_string($writerConfig['options']['db'])
            ) {
                continue;
            }
            if (!$services->has($writerConfig['options']['db'])) {
                continue;
            }

            // Retrieve the DB service from the service locator, and
            // inject it into the configuration.
            $db = $services->get($writerConfig['options']['db']);
            $config['writers'][$index]['options']['db'] = $db;
        }
    }
}
