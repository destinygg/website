#!/usr/bin/env php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($argc <= 2)
    die("First argument the file to write to, all other arguments should be ipaddresses");

$ports = array(
    '80',
    '443',
    '9998',
    '9997',
);
$torurl = "https://check.torproject.org/cgi-bin/TorBulkExitList.py?ip=_IP_&port=_PORT_";
$iplist = array();
$urls   = array();
foreach( $argv as $k => $ip ) {
    if ($k <= 1)
        continue;

    foreach( $ports as $port ) {
        $url = strtr(
            $torurl,
            array(
                '_IP_'   => urlencode( $ip ),
                '_PORT_' => $port,
            )
        );

        echo "# Getting exit node IPs from ", $url, "\n";
        $ips = file_get_contents( $url );
        if (!$ips)
            die("Could not fetch IPs");

        $ips = explode("\n", $ips );
        foreach( $ips as $key => $value ) {
            $value = trim($value);
            if ( !$value or $value[0] == '#' )
                continue;

            $iplist[ md5($value, true) ] = $value;
        }
    }
}

if (empty( $iplist ))
    die("Did not find valid IPs");
else
    echo "Found ", number_format( count( $iplist ) ), " ip addresses\n";

echo "Writing them to ", $argv[1], "\n";
$f = fopen($argv[1], "w");

foreach( $iplist as $blockedip )
    fwrite($f, $blockedip . " 1\n");

fclose($f);
echo "Done\n";
