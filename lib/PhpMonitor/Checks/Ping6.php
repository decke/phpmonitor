<?php

namespace PhpMonitor\Checks;

class Ping
{
    /* Sends an ICMPv6 echo request (ping) to the host (IPv6 only) and
     * returns the time in milliseconds.
     */
    function execute($host)
    {
        $package = "\x80\x00\x19\x2f\x00\x00\x00\x00\x70\x69\x6e\x67";

        /* create the socket, the last '1' denotes ICMP */   
        $socket = socket_create(AF_INET6, SOCK_RAW, getprotobyname('icmp6'));
 
        /* set socket receive timeout to 3 seconds */
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 3, 'usec' => 0));

        if(!$addr = dns_get_record($host, DNS_AAAA)){
            return false;
        }
        $host = $addr[0]['ipv6'];

        /* connect to socket */
        socket_connect($socket, $host, null);

        /* record start time */
        $start_time = microtime(true);

        socket_send($socket, $package, strlen($package), 0);

        if(@socket_read($socket, 255)) {
            return (microtime(true) - $start_time) * 1000;
        } else {
            return false;
        }
 
        socket_close($socket);
    }
}

