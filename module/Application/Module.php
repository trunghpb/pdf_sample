<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module {

    public function onBootstrap(MvcEvent $e) {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $serviceManager = $e->getApplication()->getServiceManager();
        $shareManager = $eventManager->getSharedManager();

        $shareManager->attach('Zend\Mvc\Application', 'dispatch.error', function($e) use ($serviceManager) {
            if ($e->getParam('exception')) {
                $serviceManager->get('Zend\Log\Logger')->crit($e->getParam('exception'));
            }
        });
    }

    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig() {
        return [
            'factories' => [
                'Zend\Log\Logger' => function($sm) {
                    $logger = new Logger;
                    $basePath = dirname(dirname(dirname(__FILE__)));
                    $writer = new Stream($basePath.'/data/log/' . date('Y-m-d') . '.log');

                    $logger->addWriter($writer);

                    return $logger;
                },
            ]
        ];
    }

}
