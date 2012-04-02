<?php
/*
 * Copyright (C) 2011 by HiNa <hina@bouhime.com>. All rights reserved.
 *
 * LICENSE
 *
 * This source file is subject to the 2-clause BSD License(Simplified
 * BSD License) that is bundled with this package in the file LICENSE.
 * The license is also available at this URL:
 * https://github.com/fetus-hina/Tor/blob/master/LICENSE
 */
class Tor_Http_Client {
    private $client = null; // Zend_Http_Client

    static public function getInstance() {
        return self::getInstance_()->init()->client;
    }

    static public function getInstanceAsIs() {
        return self::getInstance_()->client;
    }

    static private function getInstance_() {
        static $instance = null;
        if(is_null($instance)) {
            $instance = new self();
        }
        return $instance;
    }

    private function __construct() {
        echo __METHOD__ . "(): Creating new instance...\n";
        try {
            $client = new Zend_Http_Client();
            $client->setConfig(
                array(
                    'maxredirects'  => 10,
                    'useragent'     => 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)',
                    'timeout'       => 60,
                    'keepalive'     => true,
                    'storeresponse' => false));
            $client->setCookieJar();
            if($cookies = $this->loadCookie()) {
                $jar = $client->getCookieJar();
                foreach($cookies as $cookie) {
                    $jar->addCookie($cookie);
                }
            }
            $this->client = $client;
        } catch(Exception $e) {
            $this->client = null;
            throw $e;
        }
        echo __METHOD__ . "(): Created new instance.\n";
    }

    public function __destruct() {
        echo __METHOD__ . "(): Shuttting down instance...\n";
        $this->saveCookie();
        unset($this->client);
        echo __METHOD__ . "(): ok.\n";
    }

    private function init() {
        $this
            ->client
                ->resetParameters(true)
                ->setMethod(Zend_HTTP_CLIENT::GET)
                ->setUri('http://www.example.com:80/');
        return $this;
    }

    private function saveCookie() {
        echo __METHOD__ . "(): Cookie saving...\n";
        $cookies = array();
        foreach($this->client->getCookieJar() as $cookie) {
            if($cookie->isExpired() || $cookie->isSessionCookie()) {
                continue;
            }
            // echo __METHOD__ . "():     " . $cookie->__toString() . "\n";
            $cookies[] = $cookie;
        }
        file_put_contents(
            __DIR__ . '/../../../data/cookie.dat',
            serialize($cookies));
        echo __METHOD__ . "(): Cookie save done.\n";
        return $this;
    }

    private function loadCookie() {
        echo __METHOD__ . "(): Cookie loading...\n";
        $cookies = array();
        if(file_exists(__DIR__ . '/../../../data/cookie.dat')) {
            if($tmp = @unserialize(file_get_contents(__DIR__ . '/../../../data/cookie.dat'))) {
                $cookies = $tmp;
                // foreach($cookies as $cookie) {
                //     echo __METHOD__ . "():     " . $cookie->__toString() . "\n";
                // }
            }
        }
        echo __METHOD__ . "(): Cookie load done.\n";
        return $cookies;
    }
}
