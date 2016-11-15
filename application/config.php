<?php
date_default_timezone_set('Europe/Paris');

if(!defined('ENVIRONMENT'))
{
    if($_SERVER['HTTP_HOST'] == 'localhost')
        define('ENVIRONMENT', 'LOCAL');
    else
        define('ENVIRONMENT', 'PROD');
}

// Environment
switch(ENVIRONMENT)
{
    case 'LOCAL':
        return [
            'settings' => [
                'displayErrorDetails' => true,
                'app_path'            => __DIR__,
                'public_path'         => __DIR__ . '/../',
                'temp_dir'            => '/tmp/',
                'base_url'            => 'http://' . $_SERVER['HTTP_HOST']. '/Encheres',
                'renderer'            => [
                    'template_path' => __DIR__ . '/views/',
                ],
                'db'                  => [
                    'dbname'   => 'encheres',
                    'host'     => 'localhost',
                    'port'     => '',
                    'username' => 'root',
                    'password' => ''
                ],
            ]
        ]; break;
    default:
        return [
            'settings' => [
                'displayErrorDetails' => true,
                'app_path'            => __DIR__,
                'public_path'         => __DIR__ . '/../',
                'temp_dir'            => '/tmp/',
                'base_url'            => '',
                'renderer'            => [
                    'template_path' => __DIR__ . '/views/',
                ],
                'db'                  => [
                    'dbname'   => '',
                    'host'     => '',
                    'port'     => '',
                    'username' => '',
                    'password' => ''
                ],
            ]
        ];
}