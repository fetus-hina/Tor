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
class Tor_Jubeat_Saucer_Musiclist {
    const URL = 'http://p.eagate.573.jp/game/jubeat/saucer/p/playdata/music.html';

    static public function download() {
        echo __METHOD__ . "(): Starting download...\n";
        $result = array();
        for($p = 1; ; ++$p) {
            $last_error = null;
            for($retry = 0; $retry < 10; ++$retry) {
                try {
                    $html = self::doDownload($p);
                    self::validateHtml($html);
                    $result[$p] = $html;
                    if(!self::hasNextPage($html)) {
                        echo __METHOD__ . "(): download done.\n";
                        return $result;
                    }
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
        $node_table_ = $xpath->query('//table[@id="play_music_table"]');
        if($node_table_->length !== 1) {
            echo __METHOD__ . "(): 'play_music_table' node not found\n";
            throw new Exception('play_music_table node not found');
        }
        $node_table = $node_table_->item(0);
        unset($node_table_);
        
        $music_count = 0;
        $trs = $xpath->query('./tr', $node_table);
        if($trs->length < 1) {
            echo __METHOD__ . "(): No music data found.\n";
            throw new Exception('No music data found');
        }
        echo __METHOD__ . "(): Validate ok. count={$trs->length}\n";
    }

    static private function hasNextPage($html) {
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'CP932');
        $doc = new DOMDocument();
        if(!@$doc->loadHtml($html)) {
            echo __METHOD__ . "(): DOM parse failed.\n";
            throw new Exception('HTML parse failed');
        }
        echo __METHOD__ . "(): DOM loaded\n";
        $xpath = new DOMXpath($doc);
        //FIXME: @class はスペース区切りで複数指定できるのでこれではいけない
        $count = $xpath->query('//div[@id="music_data"]/ul[@class="pager"]/li[@class="next"]/a')->length;
        echo __METHOD__ . '(): ' . ($count > 0 ? 'Has' : 'Has not') . ' next page' . "\n";
        return $count > 0;
    }
}
