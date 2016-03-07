<?php

namespace PhpMonitor;

interface Check
{
    /* Runs the check
     */
    public function execute($url);

    /* Returns the time in milliseconds that it took to execute the check   
     */
    function getTime();
}

