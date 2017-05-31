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

class Subscribe
{
    use SubManager;
    use SQLHelper;
    use ArrayHelper;

    private $picks;

    private $bans;

    private $sectionPicks;

    private $sectionBans;

    private $time;

    private $update = false;

    public function __construct() {
        $container = \App::getContainer();
        $systemUser = $container->get(\Johncms\Api\UserInterface::class);
        $settings = $systemUser->set_forum ? unserialize($systemUser->set_forum) : [];
        $this->picks = new Elements(!is_null($settings['pick']) ? $settings['pick'] : []);
        $this->bans = new Elements(!is_null($settings['ban']) ? $settings['ban'] : []);
        $this->sectionPicks = new Elements(!is_null($settings['spick']) ? $settings['spick'] : []);
        $this->sectionBans = new Elements(!is_null($settings['sban']) ? $settings['sban'] : []);
        $this->time = $settings['reset'];
    }

    private function save() {
        $container = \App::getContainer();
        $db = $container->get(\PDO::class);
        $systemUser = $container->get(\Johncms\Api\UserInterface::class);
        $sysSettings = !empty($systemUser->set_forum) ? unserialize($systemUser->set_forum) : [];

        $settings['pick'] = $this->picks->get();
        $settings['ban'] = $this->bans->get();
        $settings['spick'] = $this->sectionPicks->get();
        $settings['sban'] = $this->sectionBans->get();
        $settings['reset'] = $this->time;
        $array = array_merge($sysSettings, $settings);
        $db->prepare('UPDATE `users` SET `set_forum` = ? WHERE `id` = ?')->execute([
                serialize($array),
                $systemUser->id,
        ]);
    }

    public function __destruct() {
        if ($this->update) {
            $this->save();
        }
    }
}
