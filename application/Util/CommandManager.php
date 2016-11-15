<?php

namespace App\Util;

class CommandManager
{
    protected $commands = [];
    
    public function __construct()
    {
        // Création des commandes
        $this->addCommand(new ImportNikonItemsCommand());
    }
    
    /**
     * @param Command $command
     */
    public function addCommand(Command $command)
    {
        $this->commands[$command->getName()] = $command;
    }

    /**
     * @param $commandName
     * @return null|Command
     */
    public function getCommand($commandName)
    {
        if($this->hasCommand($commandName))
            return $this->commands[$commandName];
        else
            return null;
    }

    /**
     * @param $commandName
     * @return bool
     */
    public function hasCommand($commandName)
    {
        return in_array($commandName, array_keys($this->commands));
    }

    /**
     * @return array[Command]
     */
    public function listCommands()
    {
        return $this->commands;
    }
}