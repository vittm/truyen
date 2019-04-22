<?php
/**
 * Created by PhpStorm.
 * User: michael.kirchner
 * Date: 02.07.18
 * Time: 10:14
 */

namespace de\orcas\core;


class Base
{
    public function renderCheckHasChildren($data) {
        if(isset($data['child'])) {
            foreach($data['child'] as $c) {
                if(count($c['data']) > 0) {
                    return true;
                }
            }
        }

        return false;
    }
}