<?php

namespace PhpMonitor;

interface Check
{
    /* Runs the check
     */
    public function execute($host);

    /* Returns the time in milliseconds that it took to execute the check   
     */
    function getTime();
}

