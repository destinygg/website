<?php
use Destiny\Common\Utils\Tpl;
?>
<style>
    .msg-chat {
        line-height: 1.65em;
        padding: 0.2em 1.2em .2em .6em;
    }
    .emote {
        z-index: 1;
        display: inline-block;
        position: relative;
        overflow: hidden;
        text-indent: -999em;
    }
    <?=$this->emoteCss?>
    <?=$this->emoteStyle?>
</style>
<div class="content-dark">
    <div class="ds-block">
        <?php if(!empty($this->emote)): ?>
        <div class="msg-chat">> Lorem ipsum dolor sit amet, consectetur adipiscing elit.</div>
        <div class="msg-chat">> Some text and then a <span class="emote <?=$this->emote['prefix']?>"></span> to round it off!</div>
        <div class="msg-chat">> Donec imperdiet massa eget nibh placerat aliquam</div>
        <div class="msg-chat">> Some text and then a <span title="<?=Tpl::out($this->emote['prefix'])?>" class="emote <?=$this->emote['prefix']?>"></span> <span class="emote <?=$this->emote['prefix']?>"></span> <span class="emote <?=$this->emote['prefix']?>"></span> spam?!</div>
        <div class="msg-chat">> Lorem ipsum dolor sit amet</div>
        <div class="msg-chat">> <span class="emote <?=$this->emote['prefix']?>"></span> egestas fringilla lacus</div>
        <div class="msg-chat">> Donec imperdiet sa eget nibh placerat aliquam mas</div>
        <?php else: ?>
            <div class="msg-chat">> <?=Tpl::out($this->error ?? "Error")?></div>
        <?php endif ?>
    </div>
</div>