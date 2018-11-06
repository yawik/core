<?php

/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2018 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

namespace Core;

use Symfony\Component\Dotenv\Dotenv;
use Zend\Config\Exception\InvalidArgumentException;
use Zend\Mvc\Application as BaseApplication;
use Zend\Stdlib\ArrayUtils;

/**
 * Class Application
 * @package Core
 */
class Application extends BaseApplication
{
    public static $VERSION;

    private static $configDir;

    /**
     * Get required modules for Yawik
     *
     * @return array
     */
    public static function getRequiredModules()
    {
        return array(
            'Zend\ServiceManager\Di',
            'Zend\Session',
            'Zend\Router',
            'Zend\Navigation',
            'Zend\I18n',
            'Zend\Filter',
            'Zend\InputFilter',
            'Zend\Form',
            'Zend\Validator',
            'Zend\Log',
            'Zend\Mvc\Plugin\Prg',
            'Zend\Mvc\Plugin\Identity',
            'Zend\Mvc\Plugin\FlashMessenger',
            'Zend\Mvc\I18n',
            'Zend\Mvc\Console',
            'Zend\Hydrator',
            'Zend\Serializer',
            'DoctrineModule',
            'DoctrineMongoODMModule',
        );
    }

    /**
     * Generate modules to be loaded for Yawik application
     *
     * @param array $loadModules
     * @return array
     */
    public static function generateModuleConfiguration($loadModules=[])
    {
        $modules = ArrayUtils::merge(
            static::getRequiredModules(),
            static::scanAdditionalModule()
        );
        $modules = ArrayUtils::merge($modules, $loadModules);
        return $modules;
    }

    /**
     * Get config directory location
     *
     * @return string Configuration directory
     */
    public static function getConfigDir()
    {
        if (is_null(static::$configDir)) {
            $dir = '';
            if (is_string($test = getenv('APP_CONFIG_DIR'))) {
                if (!is_dir($test)) {
                    throw new InvalidArgumentException('Directory in environment variable APP_CONFIG_DIR is not exists.');
                }
                $dir = realpath($test);
            } elseif (is_dir($test = getcwd().'/test/sandbox/config')) {
                // module development
                $dir = $test;
            } elseif (is_dir($test = getcwd().'/config')) {
                $dir = $test;
            }

            if (!is_dir($dir)) {
                throw new InvalidArgumentException('Can not determine which config directory to be used.');
            }

            static::$configDir = $dir;
        }
        return static::$configDir;
    }

    /**
     * @inheritdoc
     */
    public static function init($configuration = [])
    {
        if (!version_compare(PHP_VERSION, '5.6.0', 'ge')) {
            echo sprintf('<p>Sorry, YAWIK requires at least PHP 5.6.0 to run, but this server currently provides PHP %s</p>', PHP_VERSION);
            echo '<p>Please ask your servers\' administrator to install the proper PHP version.</p>';
            exit;
        }

        ini_set('display_errors', true);
        ini_set('error_reporting', E_ALL | E_STRICT);

        date_default_timezone_set('Europe/Berlin');

        if (php_sapi_name() == 'cli-server') {
            if (!static::setupCliServerEnv()) {
                return false;
            }
        }

        static::loadDotEnv();
        $configuration = static::loadConfig($configuration);
        return parent::init($configuration);
    }

    /**
     * Scan additional module in config/autoload/*.module.php files
     * return array Module lists
     */
    private static function scanAdditionalModule()
    {
        $modules = [];
        $configDir = static::getConfigDir();
        foreach (glob($configDir. '/autoload/*.module.php') as $moduleFile) {
            $addModules = require $moduleFile;
            foreach ($addModules as $addModule) {
                if (strpos($addModule, '-') === 0) {
                    $remove = substr($addModule, 1);
                    $modules = array_filter($modules, function ($elem) use ($remove) {
                        return strcasecmp($elem, $remove);
                    });
                } else {
                    if (!in_array($addModule, $modules)) {
                        $modules[] = $addModule;
                    }
                }
            }
        }
        return $modules;
    }

    /**
     * Setup php server
     * @return bool
     */
    private static function setupCliServerEnv()
    {
        $parseUrl = parse_url(substr($_SERVER["REQUEST_URI"], 1));
        $route = isset($parseUrl['path']) ? $parseUrl['path']:null;
        if (is_file(__DIR__ . '/' . $route)) {
            if (substr($route, -4) == ".php") {
                require __DIR__ . '/' . $route;     // Include requested script files
                exit;
            }
            return false;           // Serve file as is
        } else {                    // Fallback to index.php
            $_GET["q"] = $route;    // Try to emulate the behaviour of a .htaccess here.
        }
    }

    /**
     * Load environment variables from .env files
     */
    private static function loadDotEnv()
    {
        $env = getcwd().'/.env';
        if (!is_file($env)) {
            $env = getcwd().'/.env.dist';
        }
        if (is_file($env)) {
            $dotenv = new Dotenv();
            $dotenv->load($env);
        }

        //@TODO: should move this version loading to somewhere else
        $isVendor = strpos(__FILE__, 'modules')!==false || strpos(__FILE__, 'vendor') !== false;
        $version = getenv('TRAVIS') || $isVendor ? "undefined":exec('git describe');
        $branch = getenv('TRAVIS') || $isVendor ? "undefined":exec('git rev-parse --abbrev-ref HEAD', $output, $retVal);
        static::$VERSION = $version.'['.$branch.']';
    }

    /**
     * Load Application configuration
     * @param array $configuration
     * @return array
     */
    public static function loadConfig($configuration = [])
    {
        if (empty($configuration)) {
            $configFile = static::getConfigDir().'/config.php';
            if (!is_file($configFile)) {
                throw new InvalidArgumentException(sprintf(
                    'Can not load config file "%s". Please be sure that this file exists and readable',
                    $configFile
                ));
            }
            $configuration = include $configFile;
        }

        $configDir = static::getConfigDir();
        $isCli = php_sapi_name() === 'cli';

        // load modules
        $modules = $configuration['modules'];
        $modules = static::generateModuleConfiguration($modules);

        $yawikConfig = $configDir.'/autoload/yawik.config.global.php';
        $installMode = false;
        if (!$isCli && !file_exists($yawikConfig)) {
            $modules = ['Install'];
            $installMode = true;
        } elseif (in_array('Install', $modules)) {
            $modules = array_diff($modules, ['Install']);
        }

        $env = getenv('APPLICATION_ENV') ?: 'production';
        $defaults = [
            'module_listener_options' => [
                'module_paths' => [
                    './module',
                    './vendor',
                    './modules'
                ],
                // What configuration files should be autoloaded
                'config_glob_paths' => [
                    sprintf($configDir.'/autoload/{,*.}{global,%s,local}.php', $env)
                ],

                // Use the $env value to determine the state of the flag
                // caching disabled during install mode
                'config_cache_enabled' => ($env == 'production') && !$installMode,

                'config_cache_key' => $env,

                // Use the $env value to determine the state of the flag
                'module_map_cache_enabled' => ($env == 'production'),

                'module_map_cache_key' => 'module_map',

                // Use the $env value to determine the state of the flag
                'check_dependencies' => ($env != 'production'),

                'cache_dir' => getcwd().'/var/cache',
            ],
        ];

        $envConfig = [];
        $envConfigFile = $configDir.'/config.'.$env.'.php';
        if (file_exists($envConfigFile)) {
            if (is_readable($envConfigFile)) {
                $envConfig = include $envConfigFile;
            } else {
                \trigger_error(
                    sprintf('Environment config file "%s" is not readable.', $envConfigFile),
                    E_USER_NOTICE
                );
            }
        }

        // configuration file always win
        $configuration = ArrayUtils::merge($defaults, $configuration);

        // environment config always win
        $configuration = ArrayUtils::merge($configuration, $envConfig);

        // force override modules to load only install module in installation mode
        $modules = static::generateModuleConfiguration($modules);
        $configuration['modules'] = $modules;
        return $configuration;
    }
}
