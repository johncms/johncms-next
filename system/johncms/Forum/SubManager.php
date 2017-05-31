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

trait SubManager
{
    public function update() {
        $this->update = true;
    }
    
    public function reset() {
        $this->time = time();
        $this->update();
    }
    
    private function put(Elements $first, Elements $second, $value) {
        $update = false;
        if (!$first->exists($value)) {
            $first->add($value);
            $update = true;
        }
        if ($second->exists($value) & $update) {
            $second->remove($value);
        }
        !$update ?: $this->update();
    }
    
    public function subscribe($value, $type = 'pick', $section = true) {
        if (!in_array($type, ['pick', 'ban'])) {
            return false;
        }
        if (!$value) {
            return false;
        }
        if ($section) {
            $first = $this->sPicks();
            $second = $this->sBans();
        } else {
            $first = $this->picks();
            $second = $this->bans();
        }
        if ($type == 'ban') {
            list($first, $second) = [$second, $first];
        }
        $this->put($first, $second, $value);
    }
}
