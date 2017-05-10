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

trait ArrayHelper
{
    public function picks() {
        return $this->picks;
    }
    
    public function bans() {
        return $this->bans;
    }
    
    public function sPicks() {
        return $this->sectionPicks;
    }
    
    public function sBans() {
        return $this->sectionBans;
    }

    public function sections() {
        $res = [];
        $uid = substr(md5(uniqid(time())), 0, 8);
        $result = \App::getContainer()->get(\PDO::class)
            ->query("SELECT GROUP_CONCAT(`id` SEPARATOR ',') as `ids`, GROUP_CONCAT(`text` SEPARATOR '" . $uid . "') as `names` FROM `forum` WHERE `type` = 'r'")
            ->fetch();
        if ($result) {
            $keys = explode(',', $result['ids']);
            $names = explode($uid, $result['names']);
            $res = array_combine($keys, $names);
        }
        
        return $res;
    }
    
    private function getSectionTopics(Elements $array) {
        if ($array->count() > 0) {
            $result = \App::getContainer()->get(\PDO::class)
                ->query("SELECT GROUP_CONCAT(`id` SEPARATOR ',') FROM `forum` WHERE `type` = 't' AND `refid` IN(" . implode(',', $array->get()) . ")")
                ->fetchColumn();
            return !is_int($result) & !empty($result) ? explode(',', $result) : [$result];
        } else {
            return [];
        }
    }
    
    private function diff(array $section, array $array) {
        $result = [];
        foreach ($section as $value) {
            if (!in_array($value, $array)) {
                $result[] = $value;
            }
        }
        return $result;
    }

    private function getInts($type = 'pick') {
        if (!in_array($type, ['pick', 'ban'])) {
            return false;
        }
        return $type == 'pick'
            ? array_unique($this->picks()->get() + $this->getSectionTopics($this->sPicks()))
            : array_unique($this->bans()->get() + $this->getSectionTopics($this->sBans()));
    }
}
