<?php
if (!defined("ABSPATH") && !defined("DUPXABSPATH")) 
    die("");
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
if(!class_exists('DupProSnapLibStreamU')) {
class DupProSnapLibStreamU
{
    public static function streamGetLine($handle, $length, $ending)
    {
        $line = stream_get_line($handle, $length, $ending);

        if ($line === false) {
            throw new Exception('Error reading line.');
        }

        return $line;
    }
}
}