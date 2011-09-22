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
class Tor_Uri_Helper {
    static public function resolveEx($uri, $linkpath) {
        $ret_obj = !!($uri instanceof Zend_Uri);
        if(is_string($uri)) {
            if(!Zend_Uri::check($uri)) {
                return false;
            }
            $uri = Zend_Uri::factory($uri);
        }
        if(!$uri instanceof Zend_Uri) {
            return false;
        }
        if(!$uri = self::resolve($uri, $linkpath)) {
            return false;
        }
        if(!$uri = self::normalize($uri)) {
            return false;
        }
        return $ret_obj ? $uri : $uri->__toString();
    }

    static public function resolve(Zend_Uri $uri, $linkpath) {
        $result = null;
        $linkpath = str_replace("\xe2\x80\xbe", "%7e", $linkpath);
        if(Zend_Uri::check($linkpath)) {
            $result = Zend_Uri::factory($linkpath);
        } else {
            if(preg_match('/^[[:alpha:]][[:alnum:]\-+.]*:/', $linkpath)) {
                return false;
            }

            $tmpuri = clone $uri;
            $tmpuri->setPath(null);
            $tmpuri->setQuery(null);
            $tmpuri->setFragment(null);

            if(substr($linkpath, 0, 1) !== '/') {
                $linkpath = preg_replace('!/[^/]*$!', '/', $uri->getPath()) . $linkpath;
            }
            try {
                $result = Zend_Uri::factory($tmpuri->__toString() . $linkpath);
            } catch(Zend_Uri_Exception $e) {
                return false;
            }
        }
        $result->setPath(self::resolveRelativePath($result->getPath()));
        return $result;
    }

    static public function normalize($uri) {
        if($uri instanceof Zend_Uri) {
            $path = $uri->getPath();
            if(!is_null($path)) {
                $uri->setPath(self::resolveRelativePath($path));
            }
            if(($uri->getScheme() == 'http' && $uri->getPort() == 80) ||
                    ($uri->getScheme() == 'https' && $uri->getPort() == 443))
            {
                $uri->setPort(null);
            }
            return $uri;
        } elseif(is_string($uri)) {
            return self::normalize(Zend_Uri::factory($uri))->__toString();
        } else {
            return $uri;
        }
    }

    static public function resolveRelativePath($path) {
        $original_parts = explode('/', $path);
        $is_directory   = substr($path, -1, 1) == '/';
        $work_parts = array();
        foreach($original_parts as $part) {
            if($part === '.' || $part == '') {
                // nothing to do
            } elseif($part === '..') {
                if(count($work_parts) > 1) {
                    $work_parts = array_slice($work_parts, 0, count($work_parts) - 1);
                }
            } else {
                $work_parts[] = $part;
            }
        }
        if($is_directory) {
            $work_parts[] = '';
        }
        return '/' . join('/', $work_parts);
    }
}
