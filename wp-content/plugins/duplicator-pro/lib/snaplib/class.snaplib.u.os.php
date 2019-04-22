<?php
if (!defined("ABSPATH") && !defined("DUPXABSPATH")) 
    die("");
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if(!class_exists('DupProSnapLibOSU')) {
class DupProSnapLibOSU
{
    const WindowsMaxPathLength = 259;
    public static $isWindows;

    public static function init() {

        self::$isWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
    }
}

DupProSnapLibOSU::init();
}

