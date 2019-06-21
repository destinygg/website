<?php
use Destiny\Common\Utils\Tpl;
?>
<div id="alerts-container">
    <?php if(!empty($this->error)): ?>
    <div class="alert-container">
        <div class="alert alert-danger alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button>
            <strong><i class="fas fa-exclamation-triangle"></i> Error</strong>
            <div><?=Tpl::out($this->error)?></div>
        </div>
    </div>
    <?php endif ?>
    <?php if(!empty($this->success)): ?>
    <div class="alert-container">
        <div class="alert alert-info alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">&times;</button>
            <strong><i class="fas fa-check-square"></i> Success</strong>
            <div><?=Tpl::out($this->success)?></div>
        </div>
    </div>
    <?php endif ?>
</div>