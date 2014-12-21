<?php
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Country;
use Destiny\Common\Utils\Date;
use Destiny\Common\Config;
use Destiny\Commerce\SubscriptionStatus;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/commontop.php') ?>
<?php include Tpl::file('seg/google.tracker.php') ?>
<link href="<?=Config::cdnv()?>/web/css/messages.min.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdn()?>/chat/css/style.min.css" rel="stylesheet" media="screen">
</head>
<body id="messages" class="profile">
    <div id="page-wrap">

        <?php include Tpl::file('seg/top.php') ?>
        <?php include Tpl::file('seg/headerband.php') ?>
        <?php include Tpl::file('seg/alerts.php') ?>
        <?php include Tpl::file('profile/menu.php') ?>

        <section class="container collapsible active">
            <button accesskey="n" class="btn btn-default btn-primary" data-toggle="modal" data-target="#compose">New Message</button>
        </section>
      
        <section class="container collapsible active">
            <h3><span class="fa fa-fw fa-chevron-down expander"></span> Unread</h3>
            <div class="content">
                <div class="content-dark clearfix">

                    <?php if(!empty($model->inbox['unread'])): ?>
                    <table id="inbox" class="grid messages">
                        <colgroup>
                            <!-- <col class="c1"> -->
                            <col class="c2">
                            <col class="c3">
                            <col class="c4">
                        </colgroup>
                        <tbody>
                            <?php foreach($model->inbox['unread'] as $id => $thread): ?>
                            <tr data-id="<?= $id ?>">
                                <!-- <td class="selector"><i class="fa fa-circle-o"></i></td> -->
                                <td class="from">
                                    <a href="/profile/messages/<?= $id ?>"><?= Tpl::out($thread['othernick']) ?></a>
                                    <span class="count">(<?= $thread['count'] ?>)</span>
                                </td>
                                <td class="message"><span><?= Tpl::formatTextForDisplay($thread['message']) ?></span></td>
                                <td class="timestamp"><?= Tpl::calendar(Date::getDateTime($thread['timestamp']), Date::FORMAT); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <table id="inbox" class="grid messages">
                        <tbody>
                            <tr>
                                <td><span class="subtle">You have no unread messages</span></td>
                            </tr>
                        </tbody>
                    </table>
                    <?php endif; ?>

                </div>
            </div>
        </section>
      
        <section class="container collapsible active">
            <h3><span class="fa fa-fw fa-chevron-down expander"></span> Everything else</h3>

            <div class="content">
                <div class="content-dark clearfix">

                    <?php if(!empty($model->inbox['read'])): ?>
                    <table id="read" class="grid messages">
                        <colgroup>
                            <!-- <col class="c1"> -->
                            <col class="c2">
                            <col class="c3">
                            <col class="c4">
                        </colgroup>
                        <tbody>
                            <?php foreach($model->inbox['read'] as $id => $thread): ?>
                            <tr data-id="<?= $id ?>">
                                <!-- <td class="selector"><i class="fa fa-circle-o"></i></td> -->
                                <td class="from">
                                    <a href="/profile/messages/<?= $id ?>">
                                        <?= Tpl::out($thread['othernick']) ?>
                                    </a>
                                    <span class="count">(<?= $thread['count'] ?>)</span>
                                </td>
                                <td class="message"><span><?= Tpl::formatTextForDisplay($thread['message']) ?></span></td>
                                <td class="timestamp"><?= Tpl::calendar(Date::getDateTime($thread['timestamp']), Date::FORMAT); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <table id="read" class="grid messages">
                        <tbody>
                            <tr>
                                <td><span class="subtle">You have no read messages</span></td>
                            </tr>
                        </tbody>
                    </table>
                    <?php endif; ?>

                </div>
            </div>

        </section>

    </div>
  
    <?php include Tpl::file('profile/compose.php') ?>
    <?php include Tpl::file('seg/foot.php') ?>
    <?php include Tpl::file('seg/commonbottom.php') ?>

    <script src="<?=Config::cdnv()?>/web/js/messages.min.js"></script>
  
</body>
</html>