<?php

namespace App\Util;

interface CommandInterface
{
    /**
     * @param Array $args
     * @return mixed
     */
    public function execute($args);
}