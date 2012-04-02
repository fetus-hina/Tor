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
class Tor_Gate {
    const LOGIN_URL = 'https://p.eagate.573.jp/gate/p/login.html';

    static public function login() {
        echo __METHOD__ . "(): Getting log in form...\n";
        $http_client =
            Tor_Http_Client::getInstance()
                ->setUri(self::LOGIN_URL)
                ->setMethod(Zend_Http_Client::GET);
        $http_resp = $http_client->request();
        if(!$http_resp->isSuccessful()) {
            throw new Exception('Failed to get login form');
        }
        if(strpos($http_client->getUri()->getPath(), '/mypage/') !== false) {
            echo __METHOD__ . "(): Already logged in!\n";
            return true;
        }

        echo __METHOD__ . "(): Not logged in yet. Try to log in!\n";
        //FIXME: 既に取得している HTML をちゃんと解析する
        $conf = new Tor_Config_Konami();
        $http_resp =
            $http_client
                ->setUri(self::LOGIN_URL)
                ->setMethod(Zend_Http_Client::POST)
                ->setParameterPost(
                    array(
                        'KID'   => $conf->konami_id,
                        'pass'  => $conf->password))
                ->request();
        if(!$http_resp->isSuccessful()) {
            throw new Exception('Failed to post to log in form');
        }
        if(strpos($http_client->getUri()->getPath(), '/mypage/') === false) {
            echo __METHOD__ . "(): Could not log in! (Current URL:" . $http_client->getUri()->__toString() . "\n";
            throw new Exception('Failed to log in');
        }
        echo __METHOD__ . "(): Logged in!\n";
        return true;
    }
}
