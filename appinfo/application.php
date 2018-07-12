<?php
/**
 * Created by PhpStorm.
 * User: mason
 * Date: 7/12/18
 * Time: 12:46 PM
 */

namespace OCA\MapUtil\AppInfo;

use OCA\MapUtil\Controller\RecordingController;
use OCA\MapUtil\Db\RecordingMapper;
use OCP\AppFramework\App;
use OCA\MapUtil\Controller\PageController;
use OCP\AppFramework\IAppContainer;

class Application extends App {
    public function __construct(array $urlParams=array()){
        parent::__construct('maputil', $urlParams);

        $container = $this->getContainer();

        /**
         * Controllers and Services
         */
        $container->registerService('PageController', function(IAppContainer $c) {
            return new PageController(
                $c->query('Logger'),
                $c->query('AppName'),
                $c->query('Request')
            );
        });

        $container->registerService('RecordingController', function(IAppContainer $c) {
            return new RecordingController(
                $c->query('Logger'),
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('UserId')
            );
        });

        $container->registerService('RecordingMapper', function(IAppContainer $c) {
            return new RecordingMapper(
                $c->query('Logger'),
                $c->query('AppName')
            );
        });

        $container->registerService('Logger', function(IAppContainer $c) {
            return $c->query('ServerContainer')->getLogger();
        });
    }
}