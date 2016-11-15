<?php

use App\Util\CommandManager;
use Slim\Views\PhpRenderer;

$container = $app->getContainer();

// View renderer
$container['renderer'] = function ($c)
{
    $settings = $c->get('settings')['renderer'];
    $renderer = new PhpRenderer($settings['template_path']);
    $renderer->setAttributes([]);

    return $renderer;
};

// Swift Mailer
$container['mailer'] = function($c)
{
    if(ENVIRONMENT == 'PREPROD')
    {
        $mailTransport = \Swift_SmtpTransport::newInstance('smtp.gmail.com', 587, 'tls');
        $mailTransport->setUsername('')->setPassword('');
    }
    else
        $mailTransport = \Swift_MailTransport::newInstance();

    return \Swift_Mailer::newInstance($mailTransport);
};

// CSRF
//$container['csrf'] = function ($c)
//{
//    $guard = new \Slim\Csrf\Guard();
//    $guard->setFailureCallable(function ($request, $response, $next) {
//        $request = $request->withAttribute("csrf_status", false);
//        return $next($request, $response);
//    });
//    return $guard;
//};

// DB
$container['db'] = function ($c)
{
    $settings = $c->get('settings')['db'];

    $dns = 'mysql:dbname=' . $settings['dbname'];

    if(!empty($settings['host']))
    {
        $dns .= ';host=' . $settings['host'];
    }
    if(!empty($settings['port']))
    {
        $dns .= ';port=' . $settings['port'];
    }

    $username = $settings['username'];
    $password = $settings['password'];
    $options = !empty($settings['options']) ? $settings['options'] : [];

    if(!isset($options[PDO::MYSQL_ATTR_INIT_COMMAND]))
    {
        $options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES \'UTF8\'';
    }

    if(!isset($options[PDO::ATTR_PERSISTENT]))
    {
        $options[PDO::ATTR_PERSISTENT] = false;
    }

    if(!isset($options[PDO::ATTR_ERRMODE]))
    {
        $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
    }

    return new PDO($dns, $username, $password, $options);
};

// Command Manager
$container['commandmanager'] = function($c)
{
    return new CommandManager();
};