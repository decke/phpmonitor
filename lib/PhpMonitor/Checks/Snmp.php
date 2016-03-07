<?php

namespace PhpMonitor\Checks;


class Snmp implements \PhpMonitor\Check
{
    protected $time = 0;

    /* Tries to retrieve an SNMP key.
     *
     * Format: community@hostname/object
     */
    function execute($url)
    {
        if(strpos($url, '@') === false)
            $url = 'public@'.$url;

        if(strpos($url, '/') === false)
            $url .= '/sysName.0';

        $community = explode('@', $url)[0];
        $host = explode('/', explode('@', $url)[1])[0];
        $object = explode('/', $url)[1];

        /* record start time */
        $start_time = microtime(true);

        if(@snmpget($host, $community, $object, 1*1000000, 3) === false)
            return false;

        $this->time = (microtime(true) - $start_time) * 1000;

        return true;
    }

    /* Returns the time in milliseconds that it took to execute the check
     */
    function getTime()
    {
        return $this->time;
    }
}

