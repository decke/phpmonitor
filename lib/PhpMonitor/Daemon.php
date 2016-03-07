<?php

namespace PhpMonitor;


class Daemon extends \Core_Daemon
{
    protected $loop_interval = 1;

    protected function setup()
    {
    }

    protected function execute()
    {
        $this->log('execute');
    }

    protected function log_file()
    {
        return '/var/log/phpmonitor.log';
    }
}

