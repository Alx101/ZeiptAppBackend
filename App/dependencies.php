<?php
// DIC configuration

require 'Controllers/CustomerController.php';
require 'Models/Customer.php';
require 'Models/Session.php';
require 'Models/Card.php';

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    $defaultVars = [
        'assetsUrl' => $c->get('settings')['globals']['assetsUrl'],
    ];
    return new Slim\Views\PhpRenderer($settings['template_path'], $defaultVars);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// sqlite
$container['db'] = function ($c) {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($c['settings']['db']);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};

$container['CustomerController'] = function($c) {
    $ctrl = new \App\Controllers\CustomerController();
    $ctrl->sessionTime = $c->get('settings')['sessionExpire'];
    return $ctrl;
};