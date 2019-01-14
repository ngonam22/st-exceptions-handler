<?php
/**
 * Created by PhpStorm.
 * User: namngo
 * Date: 1/14/19
 * Time: 1:14 PM
 */

namespace StExceptionsHandler;

use Zend\EventManager\EventInterface;

use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements ConfigProviderInterface, BootstrapListenerInterface
{
    /**
     * Return default zend-serializer configuration for zend-mvc applications.
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * Listen to the bootstrap event
     *
     * @param \Zend\Mvc\MvcEvent|EventInterface $e
     * @return void
     */
    public function onBootstrap(EventInterface $e)
    {
        dd('im here');
    }

}