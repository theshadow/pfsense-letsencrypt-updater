#!/usr/bin/env php
<?php

/**
 * lpcu.php - The Let's Encrypt Pfsense Config Updater
 * 
 * This is a simple tool which takes in a path to a config file, a cert file, a key file
 * and a string identifier and attempts to update the certificate configuration. 
 *
 * Usage: lpcu.php <config-file> <cert-file> <key-file> <desc>
 *
 * config-file: Path to the pfsense config.xml file usually found in /config/config.xml
 * cert-file:   The certificate file generated from the acme.sh let's encrypt tool.
 * key-file:    The key file generated from the acme.sh let's encrypt tool.
 * desc:        The human readable description for the certificate configuration.
 * 
 * This tool will make a back up of the original file before attempting an update. The
 * backup should be located in the same path as CONFIG-FILE with the '.bak' extension.
 *
 * LICENSE:
 * MIT License
 * 
 * Copyright (c) 2016 Xander Guzman
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

if ($argc != 5) {
	echo "Missing arguments.", PHP_EOL;
	echo "usage: ", $argv[0], " <config-file> <cert-file> <key-file> <desc>", PHP_EOL;
	exit(1);
}

list($s, $configFile, $certFile, $keyFile, $desc) = $argv;

if (!file_exists($configFile)) {
	echo "Config file ", $configFile, " does not exist or is not readable.", PHP_EOL;
	exit(1);
}

if (!file_exists($certFile) || !is_readable($certFile)) {
	echo "Cert file ", $certFile, " does not exist or is not readable.", PHP_EOL;
	exit(1);
}

if (!file_exists($keyFile) || !is_readable($keyFile)) {
	echo "Key file ", $keyFile, " does not exist or is not readable.", PHP_EOL;
	exit(1);
}

copy($configFile, $configFile . '.bak');

$dom = new \DOMDocument();
if (! $dom->load($configFile)) {
	echo "Unable to load XML file ", $configFile, ".", PHP_EOL;
}

$beginCertText = "-----BEGIN CERTIFICATE-----";
$endCertText = "-----END CERTIFICATE-----";
$certText = file($certFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($certText === false) {
	echo "Unable to open certificate file ", $certFile, PHP_EOL;
	exit(1);
}

$certText = implode("", array_filter($certText, function ($line) use ($beginCertText, $endCertText) {
	return ! in_array($line, [$beginCertText, $endCertText]);
}));

$beginKeyText = "-----BEGIN RSA PRIVATE KEY-----";
$endKeyText = "-----END RSA PRIVATE KEY-----";
$keyText = file($keyFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($certText === false) {
	echo "Unable to open key file ", $keyText, PHP_EOL;
	exit(1);
}

$keyText = implode("", array_filter($keyText, function ($line) use ($beginKeyText, $endKeyText) {
	return ! in_array($line, [$beginKeyText, $endKeyText]);
}));

$xpath = new \DOMXPath($dom);
$descrs = $xpath->query('/pfsense/cert/descr');

foreach ($descrs as $descr) {
	if ($descr->nodeValue !== $desc) {
		continue;
	}
	foreach ($descr->parentNode->childNodes as $props) {
		if (! in_array($props->nodeName, ['crt', 'prv'])) {
			continue;
		}

		if ($props->nodeName === 'crt') {
			$props->nodeValue = $certText;
		}

		if ($props->nodeName === 'prv') {
			$props->nodeValue = $keyText;	
		}		
	}
}

$dom->formatOutput = true;
$dom->save($configFile);





