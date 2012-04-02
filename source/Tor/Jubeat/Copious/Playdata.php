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
class Tor_Jubeat_Copious_Playdata {
    const URL = 'http://p.eagate.573.jp/game/jubeat/copious/p/playdata/index.html';

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
        echo __METHOD__ . "(): starting validate...\n";
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'CP932');
        $doc = new DOMDocument();
        if(!@$doc->loadHtml($html)) {
            echo __METHOD__ . "(): DOM parse failed.\n";
            throw new Exception('HTML parse failed');
        }
        echo __METHOD__ . "(): DOM loaded\n";
        $xpath = new DOMXpath($doc);

        // データ検索のベースになるノードを見つける
        $node_main_ = $xpath->query('//div[@id="myPage"]');
        if($node_main_->length !== 1) {
            echo __METHOD__ . "(): 'myPage' node not found\n";
            throw new Exception('myPage node not found');
        }
        $node_main = $node_main_->item(0);
        unset($node_main_);
        
        // プレーヤ名
        echo __METHOD__ . "(): 名前: ";
        $nodes = $xpath->query('.//h2', $node_main);
        if($nodes->length < 1) {
            echo "NOT FOUND\n";
            throw new Exception('Player name not found');
        }
        echo $nodes->item(0)->textContent . "\n";

        // 称号
        echo __METHOD__ . "(): 称号: ";
        $nodes = $xpath->query('.//div[@class="userTitle"]', $node_main);
        if($nodes->length < 1) {
            echo "NOT FOUND\n";
            throw new Exception('User title not found');
        }
        echo $nodes->item(0)->textContent . "\n";

        // 細かいデータ
        $data_text = array(
            'アクティブグループ'        => false,
            'TOTAL BEST SCORE RANKING'  => false,
            '最終プレー'                => false,
            'プレーTUNE数'              => false);
        $data_image = array(
            'マーカー'                  => false,
            '背景'                      => false);
        $dls = $xpath->query('.//dl', $node_main);
        foreach($dls as $dl) {
            $key = trim($xpath->query('./dt', $dl)->item(0)->textContent);
            $key = preg_replace('/\s+\d+$/', '', $key);
            if(array_key_exists($key, $data_text)) {
                echo __METHOD__ . '(): ' . $key . ': ';
                $nodes = $xpath->query('./dd', $dl);
                if($nodes->length > 0) {
                    $val = trim($nodes->item(0)->textContent);
                    echo $val . "\n";
                    $data_text[$key] = ($val != '');
                } else {
                    echo "NOT FOUND\n";
                }
            }
            if(array_key_exists($key, $data_image)) {
                echo __METHOD__ . '(): ' . $key . ': ';
                $nodes = $xpath->query('./dd//img', $dl);
                if($nodes->length > 0) {
                    $val = trim($nodes->item(0)->getAttribute('alt'));
                    echo $val . "\n";
                    $data_image[$key] = ($val != '');
                } else {
                    echo "NOT FOUND\n";
                }
            }
        }
        foreach(array($data_text, $data_image) as $data) {
            foreach($data as $key => $val) {
                if(!$val) {
                    throw new Exception('validate error: ' . $key);
                }
            }
        }
        echo __METHOD__ . "(): Validate ok\n";
    }
}
