<?php

namespace PhpMonitor;

/**
 * This Daemon has been created to demonstrate the Workers API in 2.0.
 *
 * It creates two workers: a simple closure-based worker that computers factors, and
 * an object-based Prime Numbers worker.
 *
 * It runs jobs randomly and in response to signals and writes the jobs in a log to the MySQL table
 * described in the db.sql file.
 *
 */
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

