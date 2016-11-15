#!/usr/bin/php
<?php
define('CONSOLE_SCRIPT', 1);
define('ENVIRONMENT', 'LOCAL');
# EN DEVELOPPEMENT PEOLEO
define('PUBLIC_WEBPATH', __DIR__ . '/..');

use Slim\App;

require PUBLIC_WEBPATH . '/vendor/autoload.php';

session_start();

$settings = require PUBLIC_WEBPATH . '/application/config.php';

global $app;
$app = new App($settings);

require PUBLIC_WEBPATH . '/application/dependencies.php';
require PUBLIC_WEBPATH . '/application/functions.php';
require PUBLIC_WEBPATH . '/application/routes.php';

// Lecture paramètres en entrée
if(empty($argv[1]))
{
    // Affichage de l'aide
    echo(PHP_EOL . '-----------------------' . PHP_EOL);
    echo(PHP_EOL . 'Commandes disponibles :' . PHP_EOL);
    echo(PHP_EOL . '-----------------------' . PHP_EOL);
    /** @var \App\Util\Command $command */
    foreach(commandManager()->listCommands() as $command)
    {
        echo('    ' . str_pad($command->getName(), 25) . $command->getDescription() . PHP_EOL . PHP_EOL);
    }
    echo(PHP_EOL . 'Tapez le nom d\'une commande suivie de -h ou --help pour obtenir de l\'aide sur son utilisation.' . PHP_EOL);
}
else
{
    $commandName = $argv[1];
    if(commandManager()->hasCommand($commandName))
    {
        echo(commandManager()->getCommand($commandName)->execute($argv));
    }
    else
    {
        echo 'La commande ' . $commandName . ' n\'existe pas.';
    }
}