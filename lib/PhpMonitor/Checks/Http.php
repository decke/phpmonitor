<?php

namespace PhpMonitor\Checks;


class Ping implements PhpMonitor\Check
{
    protected $time = 0;

    /* Sends an ICMP echo request (ping) to the host (IPv4 only) and
     * returns the time in milliseconds.
     */
    function execute($host)
    {
        $context = stream_context_create(
            array(
                'http' => array(
                    'method' => 'HEAD',
                    'timeout' => 30
                )
            )
        );

        $start_time = microtime(true);

        if(!$fd = fopen($host, 'rb', false, $context))
        {
            return false;
        }
        else
        {
            if(stream_get_contents($fd) === false)
                return false;

            $meta = stream_get_meta_data($fd);

            fclose($fd);

	    $this->time = (microtime(true) - $start_time) * 1000;

            if(explode(' ', $meta['wrapper_data'][0])[1] != 200)
                return false;
            else
                return true;
        }
    }

    /* Returns the time in milliseconds that it took to execute the check
     */
    function getTime()
    {
        return $this->time;
    }
}

