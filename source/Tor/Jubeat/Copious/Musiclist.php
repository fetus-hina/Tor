<?php
/*
 * Copyright (C) 2011 by HiNa <hina@bouhime.com>. All rights reserved.
 *
 * LICENSE
 *
 * This source file is subject to the 2-cause BSD License(Simplified
 * BSD License) that is bundled with this package in the file LICENSE.
 * The license is also available at this URL:
 * https://github.com/fetus-hina/Tor/blob/master/LICENSE
 */
class Tor_Jubeat_Copious_Musiclist {
    const URL = 'http://p.eagate.573.jp/game/jubeat/copious/p/playdata/music.html';

    static public function download() {
        echo __METHOD__ . "(): Starting download...\n";
        $result = array();
        for($p = 1; $p <= 3; ++$p) {
            $last_error = null;
            for($retry = 0; $retry < 10; ++$retry) {
                try {
                    $html = self::doDownload($p);
                    self::validateHtml($html);
                    $result[$p] = $html;
                    $last_error = null;
                    break;  // retry
                } catch(Exception $e) {
                    echo __METHOD__ . "(): Catch exception: " . $e->getMessage() . "\n";
                    $last_error = $e;
                }
            }
            if($last_error) {
                throw $last_error;
            }
        }
        echo __METHOD__ . "(): download done.\n";
        return $result;
    }

    static private function doDownload($page) {
        echo __METHOD__ . "(): downloading(page {$page})...\n";
        $resp =
            Tor_Http_Client::getInstance()
                ->setUri(self::URL . '?' . http_build_query(array('page' => $page, 'rival_id' => ''), '', '&'))
                ->setMethod(Zend_Http_Client::GET)
                ->request();
        if(!$resp->isSuccessful()) {
            echo __METHOD__ . "(): download failed\n";
            throw new Exception('Cannot download musiclist');
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

        // 一覧のテーブルノード
        $node_table_ = $xpath->query('//table[@id="playdata1_table"]');
        if($node_table_->length !== 1) {
            echo __METHOD__ . "(): 'myPage' node not found\n";
            throw new Exception('myPage node not found');
        }
        $node_table = $node_table_->item(0);
        unset($node_table_);
        
        $music_count = 0;
        $trs = $xpath->query('./tr', $node_table);
        foreach($trs as $tr) {
            $tds = $xpath->query('./td', $tr);
            if($tds->length !== 4) {
                continue;
            }
            // echo __METHOD__ . "(): " . trim($tds->item(0)->textContent) . ": ";
            // for($i = 1; $i < 4; ++$i) {
            //     echo trim($tds->item($i)->textContent) . ' ';
            // }
            // echo "\n";
            ++$music_count;
        }
        if($music_count < 1) {
            echo __METHOD__ . "(): No music data found.\n";
            throw new Exception('No music data found');
        }
        echo __METHOD__ . "(): Validate ok. count={$music_count}\n";
    }
}
