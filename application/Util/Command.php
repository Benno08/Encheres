<?php

namespace App\Util;

abstract class Command implements CommandInterface
{
    const RED_START = '\033[31m';
    const BOLD_START = '\033[1m';
    const FORMAT_END = '\033[0m';

    protected $name;
    protected $description;
    protected $help;

    /**
     * @param Array $args
     * @return null|string
     */
    public function execute($args)
    {
        $output = null;
        if(in_array($args[2], ['-h', '--help']))
        {
            $output = $this->getHelp();
        }
        return $output;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getHelp()
    {
        return $this->help;
    }
}