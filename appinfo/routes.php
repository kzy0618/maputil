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
        ['name' => 'recording#getCities', 'url' => '/cities', 'verb' => 'GET'],
        ['name' => 'recording#getSuburbs', 'url' => '/suburbsAt/{city}', 'verb' => 'GET'],
        ['name' => 'recording#showRecordings', 'url' => '/recordings/{city}/{suburb}', 'verb' => 'GET'],
        ['name' => 'recording#updateWithUrl', 'url' => '/recordings/{id}', 'verb' => 'PUT']
    ]
]);