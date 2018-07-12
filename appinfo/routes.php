<?php
/**
 * Created by PhpStorm.
 * User: mason
 * Date: 7/12/18
 * Time: 12:42 PM
 */


namespace OCA\MapUtil\AppInfo;

$application = new Application();
$application->registerRoutes($this, [
    'routes' => [
        [
            // The handler is the PageController's index method
            'name' => 'page#index',
            // The route
            'url' => '/',
            // Only accessible with GET requests
            'verb' => 'GET'
        ],
        ['name' => 'recording#index', 'url' => '/recordings', 'verb' => 'GET'],
        ['name' => 'recording#show', 'url' => '/recordings/{id}', 'verb' => 'GET'],
        ['name' => 'recording#updateWithUrl', 'url' => '/recordings/{id}', 'verb' => 'PUT'], // this one takes id from url
        ['name' => 'recording#updateWithoutUrl', 'url' => '/recordings', 'verb' => 'PUT'] // this one takes data from ajax body
    ]
]);