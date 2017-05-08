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

trait SQLHelper
{
    private function getPicks() {
        $array = $this->diff($this->getInts(), $this->bans()->get());
        return count($array) > 0 ? ' AND `id` IN(' . implode(',', $array) . ')' : '';
    }
    
    private function getBans() {
        $array = $this->diff($this->getInts('ban'), $this->picks()->get());
        return count($array) > 0 ? ' AND `id` NOT IN(' . implode(',', $array) . ')' : '';
    }
    
    private function getTime() {
        return $this->time;
    }
    
    public function where() {
        return $this->getPicks() . (!$this->getPicks() ? $this->getBans() : '');
    }
    
    public function whereTime() {
        return $this->where() . ' AND `time` > ' . $this->getTime();
    }
}
