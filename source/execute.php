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
require_once(__DIR__ . '/bootstrap.php');

Tor_Gate::login();
$html_player = Tor_Jubeat_Copious_Playdata::download();
$htmls_music = Tor_Jubeat_Copious_Musiclist::download();
Tor_Jubegraph_Copious::update($html_player, $htmls_music[1], $htmls_music[2], $htmls_music[3], $htmls_music[4]);
