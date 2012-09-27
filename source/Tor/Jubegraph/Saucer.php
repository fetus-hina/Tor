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
class Tor_Jubegraph_Saucer {
    const URL = 'http://jubegraph.dyndns.org/jubeat_saucer/registFile.cgi';

    static public function update($html_player, array $html_musics) {
        echo __METHOD__ . "(): Starting upload...\n";
        $client =
            Tor_Http_Client::getInstance()
                ->setUri(self::URL)
                ->setMethod(Zend_Http_Client::POST)
                ->setFileUpload('player.html', 'playerData', $html_player, 'text/html');
        foreach($html_musics as $i => $html) {
            $p = $i + 1;
            $client->setFileUpload("music{$p}.html", "musicData{$p}", $html, 'text/html');
        }
        $resp = $client->request();
        if(!$resp->isSuccessful()) {
            echo __METHOD__ . "(): Jubegraph upload failed\n";
            throw new Exception('Jubegraph post failed');
        }
        $form = self::parseConfirmPage($client->getUri(), $resp->getBody());

        // COMMIT
        echo __METHOD__ . "(): Committing...\n";
        $client
            ->resetParameters()
            ->setUri($form->uri)
            ->setMethod($form->method)
            ->setParameterPost($form->params);
        $resp = $client->request();
        if(!$resp->isSuccessful()) {
            echo __METHOD__ . "(): Jubegraph commit failed\n";
            throw new Exception('Jubegraph commit failed');
        }
        echo __METHOD__ . "(): ok. Jubegraph updated.\n";
    }

    static private function parseConfirmPage(Zend_Uri $uri, $html) {
        echo __METHOD__ . "(): start\n";
        $doc = new DOMDocument();
        $doc->preserveWhitespace = false;
        if(!@$doc->loadHtml($html)) {
            echo __METHOD__ . "(): DOM parse failed\n";
            throw new Exception('DOM parse failed');
        }
        echo __METHOD__ . "(): DOM parse ok\n";
        $xpath = new DOMXpath($doc);
        $forms = $xpath->query('//form');
        for($i = 0; $i < $forms->length; ++$i) {
            if($result = self::parseConfirmPageNode($uri, $forms->item($i))) {
                echo __METHOD__ . "(): parse ok\n";
                return $result;
            }
        }
        echo __METHOD__ . "(): Confirm page error.\n";
        throw new Exception('Confirm page error');
    }

    static private function parseConfirmPageNode(Zend_Uri $uri, DOMElement $form) {
        echo __METHOD__ . "(): start\n";
        $result =
            array(
                'uri'       => Tor_Uri_Helper::resolveEx($uri, $form->getAttribute('action'))->__toString(),
                'referrer'  => $uri->__toString(),
                'method'    => trim(strtolower($form->getAttribute('method'))) === 'post' ? 'POST' : 'GET',
                'params'    => array());
        for($node = $form->firstChild; $node; $node = $node->nextSibling) {
            $result['params'] = array_merge($result['params'], self::parseConfirmPageNodeChild($node));
        }
        if(isset($result['params']['rid'])) {
            echo __METHOD__ . "(): ok.\n";
            return (object)$result;
        }
        echo __METHOD__ . "(): parameter rid does not exists\n";
        return false;
    }

    static private function parseConfirmPageNodeChild(DOMNode $node) {
        if($node->nodeType !== XML_ELEMENT_NODE) {
            return array();
        }
        $result = array();
        switch(strtolower($node->nodeName)) {
            case 'input':
                $result[ $node->getAttribute('name') ] = $node->getAttribute('value');
                break;
        }
        for($child = $node->firstChild; $child; $child = $child->nextSibling) {
            $result = array_merge($result, self::parseConfirmPageNodeChild($child));
        }
        return $result;
    }
}
