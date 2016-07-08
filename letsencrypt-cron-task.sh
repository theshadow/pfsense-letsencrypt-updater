#!/usr/bin/env sh
################################################################################
# This script is the controller for updating pfsense with new letsencrypt 
# certificates.
#
# Usage: letsencrypt-cron-task.sh <fqdn> <config-file> <config-desc> <lpcu.php>
#
# fqdn:      The fully qualified domain you're renewing the certificate for.
# config-file: Path to the pfsense XML configuration file.
# config-desc: The human readable string linked to a specific certificate 
#              configuration.
# lpcu.php     Full path to the lpcu.php script.
#
# LICENSE:
# MIT License
#
# Copyright (c) 2016 Xander Guzman
# 
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in all
# copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE.
################################################################################

if [ "$#" -neq 5 ]; then
	echo "Usage: " $0 "<fqdn> <config-file> <config-desc> <lpcu.php>"
fi

if [ ! -e "$2" ]; then
	echo "The config file " $2 "does not exist or is not readable."
	exit 1
fi

if [ ! -e "$4" ]; then
	echo "lpcu.php file does not exist or is not readable."
	exit 1
fi

FQDN=$1
CONFIGFILE=$2
CONFIGDESC=$3
LPCUPHP=$4

/usr/bin/env sh "/root/.acme.sh"/acme.sh --renew --cron --home "/root/.acme.sh" -d $FQDN > /dev/null
/usr/bin/env php $LPCUPHP $CONFIGFILE /root/.acme.sh/$FQDN/$FQDN.cer /root/.acme.sh/$FQDN/$FQDN.key $FQDN > /dev/null
rm /tmp/config.cache