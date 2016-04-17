#!/bin/sh
# https://certificatetools.com/
openssl req -new -keyout dgg.key -out dgg.csr -newkey rsa:4096 -sha256 -config openssl.cnf -nodes -subj '/C=US/ST=Nebraska/L=Omaha/O=Destiny.gg/CN=*.destiny.gg'
