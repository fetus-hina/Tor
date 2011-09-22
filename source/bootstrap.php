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
date_default_timezone_set('Asia/Tokyo');
mb_internal_encoding('UTF-8');
set_include_path(__DIR__ . PATH_SEPARATOR . get_include_path());

require_once('Zend/Loader/Autoloader.php');
$autoloader =
    Zend_Loader_Autoloader::getInstance()
        ->unregisterNamespace(array('Zend_', 'ZendX_'))
        ->setFallbackAutoloader(true);
