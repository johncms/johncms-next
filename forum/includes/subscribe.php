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

defined('_IN_JOHNCMS') or die('Error: restricted access');

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Johncms\Api\UserInterface $systemUser */
$systemUser = $container->get(Johncms\Api\UserInterface::class);

/** @var Johncms\Api\ToolsInterface $tools */
$tools = $container->get(Johncms\Api\ToolsInterface::class);

$i = 0;
                                  
$subscribe = new Johncms\Forum\Subscribe();
#echo $subscribe->whereTime();

// Закрываем доступ
if (!$systemUser->isValid()) {
    require('../system/head.php');
    echo $tools->displayError(_t('Access forbidden'));
    require('../system/end.php');
    exit;
}

$textl = _t('Forum') . ' | ' . _t('Unread');
$headmod = 'forumnew';
require('../system/head.php');

switch ($do) {
    case 'pick':
        $pick = isset($_GET['pick']) ? abs(intval($_GET['pick'])) : 0;
        $subscribe->subscribe($pick, 'pick', false);
        break;

    case 'ban':
        $ban = isset($_GET['ban']) ? abs(intval($_GET['ban'])) : 0;
        $subscribe->subscribe($ban, 'ban', false);
        break;
        
    case 'reset':
        $subscribe->reset();
        break;
        
    case 'sections' :
        echo '<div class="phdr"><a href="index.php"><b>' . _t('Forum') . '</b></a> | ' . _t('Unread') . '</div>';
        echo '<div class="gmenu">';
        if (!isset($_POST['submit'])) {
            $sections = $subscribe->sections();
            if (count($sections) > 0) {
                $picks = $subscribe->sPicks()->get();
                $bans = $subscribe->sBans()->get();
                echo '<form action="?act=subscribe&amp;do=sections" method="post">';
                foreach ($sections as $key => $value) {
                    echo '<div class="list' . (++$i % 2 ? 2 : 1) . '">';
                    echo '<div>' . _t('Pick') . ' <input type="radio" name="values[' . $key . ']" value="pick" ' . (in_array($key, $picks) ? ' checked="checked"' : '') . '>  ' . _t('Ban') . ' <input type="radio" name="values[' . $key . ']" value="ban" ' . (in_array($key, $bans) ? ' checked="checked"' : '') . '> ' . $value . '</div>';
                    echo '</div>';
                }
                echo '<br><input type="submit" name="submit">';
                echo '</form>';
            }
        } else {
            foreach ($_POST['values'] as $key => $value) {
                $subscribe->subscribe($key, $value);
            }
        }
        echo '<div><a href="?act=subscribe">' . _t('Back') . '</a></div></div>';
    break;

    case 'interval':
        // Показ новых тем за выбранный период
        $vr = isset($_REQUEST['vr']) ? abs(intval($_REQUEST['vr'])) : 24;

        $count = $db->query("SELECT COUNT(*) FROM `forum` WHERE `type`='t' AND `time` > UNIX_TIMESTAMP(NOW() - INTERVAL " . $vr . " HOUR) AND `close` != 1 " . $subscribe->where())->fetchColumn();

        echo '<div class="phdr"><a href="index.php"><b>' . _t('Forum') . '</b></a> | ' . sprintf(_t('All for period %d hours'), $vr) . '</div>';

        // Форма выбора периода времени
        echo '<div class="topmenu"><form action="index.php?act=subscribe&amp;do=interval" method="post">' .
        '<input type="text" maxlength="3" name="vr" value="' . $vr . '" size="3"/>' .
        '<input type="submit" name="submit" value="' . _t('Show period') . '"/>' .
        '</form></div>';

        if ($count > $kmess) {
            echo '<div class="topmenu">' . $tools->displayPagination('index.php?act=subscribe&amp;do=interval&amp;vr=' . $vr . '&amp;', $start, $count, $kmess) . '</div>';
        }

        if ($count) {
            $req = $db->query("SELECT * FROM `forum` WHERE `type`='t' AND `time` > '" . $vr1 . "' AND `close` != 1 " . $subscribe->where() . " ORDER BY `time` DESC LIMIT " . $start . "," . $kmess);

            for ($i = 0; $res = $req->fetch(); ++$i) {
                echo $i % 2 ? '<div class="list2">' : '<div class="list1">';
                $razd = $db->query("SELECT `id`, `refid`, `text` FROM `forum` WHERE `type`='r' AND `id`=" . $res['refid'])->fetch();
                $frm = $db->query("SELECT `text` FROM `forum` WHERE `type`='f' AND `id`=" . $razd['refid'])->fetch();
                $colmes = $db->query("SELECT * FROM `forum` WHERE `refid` = " . $res['id'] . " AND `type` = 'm' AND `close` != 1 ORDER BY `time` DESC");
                $colmes1 = $colmes->rowCount();
                $cpg = ceil($colmes1 / $kmess);
                $nick = $colmes->fetch();

                if ($res['edit']) {
                    echo $tools->image('tz.gif');
                } elseif ($res['close']) {
                    echo $tools->image('dl.gif');
                } else {
                    echo $tools->image('np.gif');
                }

                if ($res['realid'] == 1) {
                    echo $tools->image('rate.gif');
                }

                echo '&#160;<a href="index.php?id=' . $res['id'] . ($cpg > 1 && $set_forum['upfp'] && $set_forum['postclip'] ? '&amp;clip' : '') . ($set_forum['upfp'] && $cpg > 1 ? '&amp;page=' . $cpg : '') . '">' . (empty($res['text']) ? '-----' : $res['text']) .
                '</a>&#160;[' . $colmes1 . ']';
                if ($cpg > 1) {
                    echo '<a href="index.php?id=' . $res['id'] . (!$set_forum['upfp'] && $set_forum['postclip'] ? '&amp;clip' : '') . ($set_forum['upfp'] ? '' : '&amp;page=' . $cpg) . '">&#160;&gt;&gt;</a>';
                }

                echo '<br /><div class="sub"><a href="index.php?id=' . $razd['id'] . '">' . $frm['text'] . '&#160;/&#160;' . $razd['text'] . '</a><br />';
                echo $res['from'];

                if ($colmes1 > 1) {
                    echo '&#160;/&#160;' . $nick['from'];
                }

                echo ' <span class="gray">' . $tools->displayDate($nick['time']) . '</span>';
                echo '</div></div>';
            }
        } else {
            echo '<div class="menu"><p>' . _t('There is nothing new in this forum for selected period') . '</p></div>';
        }

        echo '<div class="phdr">' . _t('Total') . ': ' . $count . '</div>';

        if ($count > $kmess) {
            echo '<div class="topmenu">' . $tools->displayPagination('index.php?act=subscribe&amp;do=interval&amp;vr=' . $vr . '&amp;', $start, $count, $kmess) . '</div>' .
            '<p><form action="index.php?act=new&amp;do=period&amp;vr=' . $vr . '" method="post">
            <input type="text" name="page" size="2"/>
            <input type="submit" value="' . _t('To Page') . ' &gt;&gt;"/></form></p>';
        }    

        break;

    default:
        $count = $db->query("SELECT COUNT(*) FROM `forum` WHERE `type`='t' AND `close` != 1 " . $subscribe->whereTime())->fetchColumn();

        echo '<div class="phdr"><a href="index.php"><b>' . _t('Forum') . '</b></a> | ' . _t('Unread') . '</div>';
        echo '<div class="gmenu"><p><a href="?act=subscribe&amp;do=interval">' . _t('Show period') . '</a></p>';
        echo '<p><a href="?act=subscribe&amp;do=sections">' . _t('My subscriptions') . '</a></p></div>';

        $nav = $count > $kmess ? '<div class="topmenu">' . $tools->displayPagination('index.php?act=subscribe&amp;', $start, $count, $kmess) . '</div>' : '';
        echo $nav;

        if ($count) {
            $req = $db->query("SELECT * FROM `forum` WHERE `type`='t' AND `close` != 1 " . $subscribe->whereTime() . " ORDER BY `time` DESC LIMIT " . $start . "," . $kmess);
            while ($res = $req->fetch()) {
                echo '<div class="list' . (++$i % 2 ? 2 : 1) . '">';
                $razd = $db->query("SELECT `id`, `refid`, `text` FROM `forum` WHERE `type`='r' AND `id`=" . $res['refid'])->fetch();
                $frm = $db->query("SELECT `text` FROM `forum` WHERE `type`='f' AND `id`=" . $razd['refid'])->fetchColumn();
                $colmes = $db->query("SELECT `from`, `time` FROM `forum` WHERE `refid` = " . $res['id'] . " AND `type` = 'm' ORDER BY `time` DESC");
                $colmes1 = $colmes->rowCount();
                $cpg = ceil($colmes1 / $kmess);
                $nick = $colmes->fetch();
                
                echo $tools->image($res['edit'] ? 'tz.gif' : ($res['close'] ? 'dl.gif' : 'np.gif'));
                echo $res['realid'] == 1 ? $tools->image('rate.gif') : '';

                echo '&#160;<a href="index.php?id=' . $res['id'] . ($cpg > 1 && $set_forum['upfp'] && $set_forum['postclip'] ? '&amp;clip' : '') . ($set_forum['upfp'] && $cpg > 1 ? '&amp;page=' . $cpg : '') . '">' . (empty($res['text']) ? '-----' : $res['text']) .
                '</a>&#160;[' . $colmes1 . ']';
                if ($cpg > 1) {
                    echo '<a href="index.php?id=' . $res['id'] . (!$set_forum['upfp'] && $set_forum['postclip'] ? '&amp;clip' : '') . ($set_forum['upfp'] ? '' : '&amp;page=' . $cpg) . '">&#160;&gt;&gt;</a>';
                }

                echo '<br /><div class="sub"><a href="index.php?id=' . $razd['id'] . '">' . $frm . '&#160;/&#160;' . $razd['text'] . '</a><br />';
                echo $res['from'];

                if ($colmes1 > 1) {
                    echo '&#160;/&#160;' . $nick['from'];
                }

                echo ' <span class="gray">' . $tools->displayDate($nick['time']) . '</span>';
                echo '</div></div>';
            }
        } else {
            echo '<div class="menu"><p>' . _t('The list is empty') . '</p></div>';
        }

        echo '<div class="phdr">' . _t('Total') . ': ' . $count . '</div>';

        echo $nav;

        if ($count) {
            echo '<p><a href="index.php?act=subscribe&amp;do=reset">' . _t('Mark as read') . '</a></p>';
        }
}

if (in_array($do, ['pick', 'ban', 'reset'])) {
    $db->prepare('UPDATE `users` SET `set_forum` = ? WHERE `id` = ?')->execute([
        serialize($set_forum),
        $systemUser->id,
    ]);
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
