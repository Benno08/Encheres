<?php

use Slim\App;

/**
 * @return App
 */
function app()
{
    global $app;

    return $app;
}

/**
 * @param string $service
 * @return mixed
 */
function di($service)
{
    global $app;

    return $app->getContainer()->get($service);
}

/**
 * @return \Swift_Mailer
 */
function mailer()
{
    return di('mailer');
}

/**
 * @param string $template
 * @param array  $data
 * @return string
 */
function template($template, array $data = [])
{
    return di('renderer')->fetch($template, $data);
}

/**
 * Retourne une instance de connexion PDO à la base de données.
 * @return \PDO
 */
function db()
{
    return di('db');
}

/**
 * @return \App\Util\CommandManager
 */
function commandManager()
{
    return di('commandmanager');
}

/**
 * @param string $path
 * @return string
 */
function baseUrl($path = null)
{
    return rtrim(di('request')->getUri()->getBasePath(), '/') . ($path ? '/' . ltrim($path, '/') : '');
}

/**
 * @param string $value
 * @return string
 */
function escape($value)
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8');
}

/**
 * Génère une chaîne alpha-numérique aléatoire.
 * @param int $length Taille de la chaîne à produire.
 * @return string
 */
function generateString($length = 16)
{
    $alphabet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $alphabetLength = strlen($alphabet);
    $salt = '';
    for($i = 0; $i < $length; $i++)
    {
        $salt .= $alphabet[rand(0, $alphabetLength - 1)];
    }

    return $salt;
}
