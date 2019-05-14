<?php

namespace PhpMonitor\Checks;


class Http implements \PhpMonitor\Check
{
    protected $time = 0;

    /* Sends an ICMP echo request (ping) to the host (IPv4 only) and
     * returns the time in milliseconds.
     */
    function execute($url)
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

        if(!$fd = @fopen($url, 'rb', false, $context)) {
            return false;
        }
        else {
            if(stream_get_contents($fd) === false) {
                return false;
            }

            $meta = stream_get_meta_data($fd);

            fclose($fd);

	    $this->time = (microtime(true) - $start_time) * 1000;

            return (explode(' ', $meta['wrapper_data'][0])[1] == 200);
        }
    }

    /* Returns the time in milliseconds that it took to execute the check
     */
    function getTime()
    {
        return $this->time;
    }
}

