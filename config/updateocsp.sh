#!/bin/sh
openssl ocsp -noverify -issuer /etc/ssl/certs/COMODORSADomainValidationSecureServerCA.crt \
    -cert /etc/ssl/certs/destiny.gg.wildcard_combined.crt -url "http://ocsp.comodoca.com" \
    -respout /etc/ssl/certs/destiny.gg.wildcard_combined.crt.ocsp > /dev/null && \
echo "set ssl ocsp-response $(/usr/bin/base64 -w 10000 /etc/ssl/certs/destiny.gg.wildcard_combined.crt.ocsp)" | \
/usr/bin/socat stdio /var/run/haproxy.stats
