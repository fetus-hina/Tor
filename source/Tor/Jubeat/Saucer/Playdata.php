<?php
/*
 * Copyright (C) 2012 by HiNa <hina@bouhime.com>. All rights reserved.
 *
 * LICENSE
 *
 * This source file is subject to the 2-clause BSD License(Simplified
 * BSD License) that is bundled with this package in the file LICENSE.
 * The license is also available at this URL:
 * https://github.com/fetus-hina/Tor/blob/master/LICENSE
 */
class Tor_Jubeat_Saucer_Playdata {
    const URL = 'http://p.eagate.573.jp/game/jubeat/saucer/p/playdata/index.html';

    static public function download() {
        echo __METHOD__ . "(): Starting download...\n";
        $last_error = null;
        for($retry = 0; $retry < 10; ++$retry) {
            try {
                $html = self::doDownload();
                self::validateHtml($html);
                echo __METHOD__ . "(): download done.\n";
                return $html;
            } catch(Exception $e) {
                echo __METHOD__ . "(): Catch exception: " . $e->getMessage() . "\n";
                $last_error = $e;
            }
        }
        throw $last_error;
    }

    static private function doDownload() {
        echo __METHOD__ . "(): downloading...\n";
        $resp =
            Tor_Http_Client::getInstance()
                ->setUri(self::URL)
                ->setMethod(Zend_Http_Client::GET)
                ->request();
        if(!$resp->isSuccessful()) {
            echo __METHOD__ . "(): download failed\n";
            throw new Exception('Cannot download playdata');
        }
        $html = $resp->getBody();
        echo __METHOD__ . "(): download done.\n";
        return $html;
    }

    static private function validateHtml($html) {
        //TODO
    }
}
