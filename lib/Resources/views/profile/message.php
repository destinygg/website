<?php
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Date;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/commontop.php') ?>
<?php include Tpl::file('seg/google.tracker.php') ?>

<link href="<?=Config::cdn()?>/web/css/messages.min.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdn()?>/chat/css/style.min.css" rel="stylesheet" media="screen">

</head>
<body id="message" class="profile">

    <div id="page-wrap">

        <?php include Tpl::file('seg/top.php') ?>
        <?php include Tpl::file('seg/headerband.php') ?>
        <?php include Tpl::file('seg/alerts.php') ?>
        <?php include Tpl::file('profile/menu.php') ?>
        
        <section class="container message-list active">

            <h4 class="message-list-title">Messages between you and <?= Tpl::out($model->message['from']) ?></h4>

            <?php for($i=count($model->messages)-1; $i>=0; $i--): ?>
            <?php
                $msg = $model->messages[$i];
                $isme = (stristr($msg['from'], $model->username) !== false);
                $styles = array();
                $styles[] = 'message-active';
                $styles[] = ($isme) ? 'message-me' : 'message-notme';
                $styles[] = ($msg['isread'] == 1) ? 'message-read' : 'message-unread';
            ?>
            <div id="<?= Tpl::out($msg['id']) ?>" class="message <?= join(' ', $styles) ?> content content-dark clearfix">
                <div class="message-content clearfix">
                    <div class="message-header clearfix">
                        <div class="message-from pull-left">
                            <span alt="<?= Tpl::out($msg['from']) ?>"><?= (!$isme) ? Tpl::out($msg['from']) : 'Me' ?></span>
                        </div>
                        <div class="message-date pull-right"><?= Tpl::calendar(Date::getDateTime($msg['timestamp']), Date::FORMAT); ?></div>
                    </div>
                    <div class="message-txt"><?= Tpl::formatTextForDisplay($msg['message']) ?></div>
                </div>
                <div class="message-summary clearfix">
                    <span class="message-from">
                        <span alt="<?= Tpl::out($msg['from']) ?>"><?= (!$isme) ? Tpl::out($msg['from']) : 'Me' ?></span>
                    </span>
                    <span class="message-snippet"><?= Tpl::formatTextForDisplay($msg['message']) ?></span>
                    <span class="message-date"><?= Tpl::calendar(Date::getDateTime($msg['timestamp']), Date::FORMAT); ?></span>
                </div>
                <div class="speech-arrow"></div>
            </div>
            <?php endfor; ?>
            <div class="clearfix"></div>
            <div class="message-reply content content-dark clearfix" style="">
                <div class="clearfix">
                    <span class="pull-left">
                        <a accesskey="r" id="reply-toggle" href="#reply" data-replyto="<?= $model->replyto ?>" data-toggle="modal" data-target="#compose"><i class="fa fa-reply-all"></i> Reply</a>
                        to this message or go to <a accesskey="m" href="/profile/messages">inbox</a>.
                    </span>
                </div>
            </div>
            <a name="latest"></a>

        </section>

    </div>

    <?php include Tpl::file('profile/compose.php') ?>
    <?php include Tpl::file('seg/foot.php') ?>
    <?php include Tpl::file('seg/commonbottom.php') ?>

    <script src="<?=Config::cdnv()?>/web/js/messages.min.js"></script>

    <script>
    $(window).on('load', function(){
        var offset = $('.message-list .message:last').offset().top-20;
        $('html,body').animate({scrollTop:offset},5);
    });
    </script>

</body>
</html>