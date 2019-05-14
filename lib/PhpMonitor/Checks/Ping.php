<?php

namespace PhpMonitor\Checks;


class Ping implements \PhpMonitor\Check
{
    protected $time = 0;

    /* Sends an ICMP echo request (ping) to the host (IPv4 only) and
     * returns the time in milliseconds.
     */
    function execute($url)
    {
        $host = explode('://', $url)[1];
        $package = "\x08\x00\x19\x2f\x00\x00\x00\x00\x70\x69\x6e\x67";

        /* create the socket, the last '1' denotes ICMP */   
        $socket = socket_create(AF_INET, SOCK_RAW, getprotobyname('icmp'));

        /* set socket receive timeout to 3 seconds */
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 3, 'usec' => 0));

        /* connect to socket */
        socket_connect($socket, $host, null);

        /* record start time */
        $start_time = microtime(true);

        socket_send($socket, $package, strlen($package), 0);

        if(@socket_read($socket, 255)) {
            socket_close($socket);
            $this->time = (microtime(true) - $start_time) * 1000;

            return true;
        }
        else {
            return false;
        }
    }

    /* Returns the time in milliseconds that it took to execute the check   
     */
    function getTime()
    {
        return $this->time;
    }
}

