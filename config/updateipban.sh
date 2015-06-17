#!/bin/bash
mapfile=$1

echo "clear map $mapfile" | \
/usr/bin/socat stdio /var/lib/haproxy/stats > /dev/null

while read line
do
	if ! [[ ${line:0:1} == "#" ]]; then
		echo "add map $mapfile $line 1" | \
		/usr/bin/socat stdio /var/lib/haproxy/stats > /dev/null
	fi
done < $mapfile
