<?php
namespace Destiny;
use Destiny\Common\Config;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Date;
?>
<!DOCTYPE html>
<html>
<head>
    <title><?=Tpl::title($model->title)?></title>
    <meta charset="utf-8">
    <?php include Tpl::file('seg/opengraph.php') ?>
    <?php include Tpl::file('seg/commontop.php') ?>
    <?php include Tpl::file('seg/google.tracker.php') ?>
    <link href="<?=Config::cdnv()?>/chat/css/style.min.css" rel="stylesheet" media="screen">
</head>
<body id="emoticons" class="no-brand">
<div id="page-wrap">

    <?php include Tpl::file('seg/top.php') ?>
    <?php include Tpl::file('seg/headerband.php') ?>
    <?php function calcStepClass($combo) {
        $stepClass = '';
        if($combo >= 50)
            $stepClass = ' x50';
        else if($combo >= 30)
            $stepClass = ' x30';
        else if($combo >= 20)
            $stepClass = ' x20';
        else if($combo >= 10)
            $stepClass = ' x10';
        else if($combo >= 5)
            $stepClass = ' x5';

        return $stepClass;
    } ?>

    <section class="container">
        <h1 class="title">Hall of Fame</h1>
        <hr size="1">
        <div class="col-md-4">
            <h4>[ Highest Combos ]</h4>
            <table>
                <?php foreach( $model->topCombos as $trigger ): ?>
                    <?php $time = Date::getElapsedTime(Date::getDateTime($trigger['timestamp'])) ?>
                    <tr data-placement="left" data-toggle="tooltip" title="<?=$time?>">
                        <td>
                            <a class="label label-default" data-memers="<?=$trigger['memers']?>">Memers</a>
                        </td>
                        <td>
                            <div id="chat-lines" style="margin-top: 2px; margin-bottom: 2px;">
                                <div class="emotecount <?=calcStepClass($trigger['combo'])?>"><i class="count"><?=Tpl::out($trigger['combo'])?></i><i class="x">X</i> C-C-C-COMBO</div>
                                <div style="display: inline-block" class="chat-emote chat-emote-<?=$trigger['emote']?>" data-placement="right" data-toggle="tooltip" title="<?=$trigger['emote']?>"></div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div class="col-md-4">
            <h4>[ Recent Combos ]</h4>
            <table>
                <?php foreach( $model->recentCombos as $trigger ): ?>
                    <?php $time = Date::getElapsedTime(Date::getDateTime($trigger['timestamp'])) ?>
                    <tr data-placement="left" data-toggle="tooltip" title="<?=$time?>">
                        <td>
                            <a class="label label-default" data-memers="<?=$trigger['memers']?>">Memers</a>
                        </td>
                        <td>
                            <div id="chat-lines" style="margin-top: 2px; margin-bottom: 2px;">
                                <div class="emotecount <?=calcStepClass($trigger['combo'])?>"><i class="count"><?=Tpl::out($trigger['combo'])?></i><i class="x">X</i> C-C-C-COMBO</div>
                                <div style="display: inline-block" class="chat-emote chat-emote-<?=$trigger['emote']?>" data-placement="right" data-toggle="tooltip" title="<?=$trigger['emote']?>"></div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div class="col-md-4">
            <h4>[ My Combos ]</h4>
            <?php if(isset($model->myCombos)): ?>
                <table>
                    <?php foreach( $model->myCombos as $trigger ): ?>
                        <?php $time = Date::getElapsedTime(Date::getDateTime($trigger['timestamp'])) ?>
                        <tr data-placement="left" data-toggle="tooltip" title="<?=$time?>">
                            <td>
                                <a class="label label-default" data-memers="<?=$trigger['memers']?>">Memers</a>
                            </td>
                            <td>
                                <div id="chat-lines" style="margin-top: 2px; margin-bottom: 2px;">
                                    <div class="emotecount <?=calcStepClass($trigger['combo'])?>"><i class="count"><?=Tpl::out($trigger['combo'])?></i><i class="x">X</i> C-C-C-COMBO</div>
                                    <div style="display: inline-block" class="chat-emote chat-emote-<?=$trigger['emote']?>" data-placement="right" data-toggle="tooltip" title="<?=$trigger['emote']?>"></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>You must <a href="/login" target="_parent">sign in</a> to see your combos.</p>
            <?php endif; ?>
        </div>
        <div class="row"></div>
        <div class="col-md-12">
            <h4>[ Memers ]</h4>
            <p id="memers" style="word-wrap: break-word"></p>
        </div>
    </section>

</div>

<?php include Tpl::file('seg/foot.php') ?>
<?php include Tpl::file('seg/commonbottom.php') ?>
<script>
    (function(){
        var prevElement = null;
        $('.label').click(function() {
            if (prevElement != null) {
                prevElement.removeClass('label-success');
            }

            $(this).addClass('label-success');
            $('#memers').html($(this).data('memers'));
            prevElement = $(this);
        });
    })();
</script>

</body>
</html>