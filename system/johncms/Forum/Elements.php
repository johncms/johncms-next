<?php
/*
 * JohnCMS NEXT Mobile Content Management System (http://johncms.com)
 *
 * For copyright and license information, please see the LICENSE.md
 * Installing the system or redistributions of files must retain the above copyright notice.
 *
 * @link        http://johncms.com JohnCMS Project
 * @copyright   Copyright (C) JohnCMS Community
 * @license     GPL-3
 */

namespace Johncms\Forum;

class Elements extends \ArrayObject
{
    public function add($value) {
        $this->offsetSet($value, $value);
    }
    
    public function remove($value) {
        $this->offsetUnset($value);
    }
    
    public function exists($value) {
        return $this->offsetExists($value);
    }
    
    public function get() {
        return $this->getArrayCopy();
    }
}
