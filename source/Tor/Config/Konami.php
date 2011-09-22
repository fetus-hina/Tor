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
class Tor_Config_Konami {
    private
        $konami_id  = null,
        $password   = null;

    public function __construct() {
        $ini = new Zend_Config_Ini(__DIR__ . '/../../../config/config.ini', 'konami');
        $this->konami_id = (string)$ini->konami_id;
        $this->password = (string)$ini->password;
    }

    public function __get($key) {
        switch($key) {
        case 'konami_id':   return $this->konami_id;
        case 'password':    return $this->password;
        }
    }
}
