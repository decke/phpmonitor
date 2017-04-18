<?php

namespace PhpMonitor\Checks;


class OpenVPN implements \PhpMonitor\Check
{
    protected $time = 0;

    /* Sends an UDP connection handshake to the host (IPv4 only) and
     * returns the time in milliseconds.
     */
    function execute($url)
    {
        $package = "\x38\x01\x00\x00\x00\x00\x00\x00\x00";

        $urlparts = explode('://', $url)[1];
        $host = explode(':', $urlparts)[0];

        if(count(explode(':', $urlparts)) < 2)
            $port = 1194;
        else
            $port = explode(':', $urlparts)[1];

        /* record start time */
        $start_time = microtime(true);

        /* create the UDP socket */
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        /* set socket receive timeout to 3 seconds */
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 3, 'usec' => 0));

        if(!$addr = dns_get_record($host, DNS_A)){
            return false;
        }
        $host = $addr[0]['ip'];

        socket_sendto($socket, $package, strlen($package), 0, $host, $port);

        if(socket_recvfrom($socket, $buffer, 64, 0, $host, $port) === false)
        {
            socket_close($socket);
            return false;
        }

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

