<?php
/**
 * YAWIK
 *
 * @filesource
 * @license    MIT
 * @copyright  2013 - 2016 Cross Solution <http://cross-solution.de>
 */

/** */
namespace CoreTestUtils\TestCase;

use CoreTestUtils\Mock\ServiceManager\Config as ServiceManagerMockConfig;
use CoreTestUtils\Mock\ServiceManager\PluginManagerMock;
use CoreTestUtils\Mock\ServiceManager\ServiceManagerMock;

/**
 * Creates a service manager mock with configured services.
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @since  0.25
 */
trait ServiceManagerMockTrait
{
    /**
     * The service manager mock instance
     *
     * @var ServiceManagerMock
     */
    private $serviceManagerMock;

    /**
     * The plugin manager mock instance
     *
     * @var PluginManagerMock
     */
    private $pluginManagerMock;

    /**
     *
     *
     * @var PluginManagerMock|ServiceManagerMock[]
     */
    private $__ServiceManagerMockTrait__mocks = [];

    public function tearDown()
    {
        foreach ($this->__ServiceManagerMockTrait__mocks as $mock) {
            $mock->verifyCallCount();
        }
    }

    /**
     * Create a service manager mock.
     *
     * @param array $services
     *
     * @see ServiceManagerMockConfig::configureServiceManager
     * @return ServiceManagerMock
     */
    public function createServiceManagerMock(array $services = [])
    {
        $serviceManagerMock = new ServiceManagerMock();
        if (!empty($services)) {
            $config = new ServiceManagerMockConfig(['mocks' => $services]);
            $config->configureServiceManager($serviceManagerMock);
        }

        $this->__ServiceManagerMockTrait__mocks[] = $serviceManagerMock;
        return $serviceManagerMock;
    }


    /**
     * Gets or create the service manager mock.
     *
     * @param array $services
     *
     * @see ServiceManagerMockConfig::configureServiceManager
     * @return ServiceManagerMock
     */
    public function getServiceManagerMock(array $services = [])
    {
        if (!$this->serviceManagerMock) {
            $this->serviceManagerMock = $this->createServiceManagerMock($services);
        }

        return $this->serviceManagerMock;
    }

    /**
     * Create a plugin manager mock.
     *
     * @param array|\Zend\ServiceManager\ServiceLocatorInterface $services
     * @param null|int|\Zend\ServiceManager\ServiceLocatorInterface  $parent
     * @param int   $count
     *
     * @return PluginManagerMock
     */
    public function createPluginManagerMock($services = [], $parent = null, $count = 1)
    {

        if (is_array($services)) {
            $config = new ServiceManagerMockConfig(['mocks' => $services]);
        } else {
            $config = null;
            $count = is_int($parent) ? $parent : $count;
            $parent = $services;
        }

        $pluginManagerMock = new PluginManagerMock($config);

        if (null !== $parent) {
            $pluginManagerMock->setServiceLocator($parent, $count);
        }

        $this->__ServiceManagerMockTrait__mocks[] = $pluginManagerMock;
        return $pluginManagerMock;
    }

    /**
     * Gets or create the plugin manager mock.
     *
     * @param array|\Zend\ServiceManager\ServiceLocatorInterface $services
     * @param null|int|\Zend\ServiceManager\ServiceLocatorInterface  $parent
     * @param int   $count
     *
     * @return PluginManagerMock
     */
    public function getPluginManagerMock($services = [], $parent = null, $count = 1)
    {
        if (!$this->pluginManagerMock) {
            $this->pluginManagerMock = $this->createPluginManagerMock($services, $parent, $count);
        }

        return $this->pluginManagerMock;
    }
}