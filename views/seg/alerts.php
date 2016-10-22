<?php
use Destiny\Common\Utils\Tpl;
?>
<div id="alerts-container"></div>

<?php if(!empty($this->error)): ?>
<section class="container">
    <div class="alert alert-danger" style="margin:0;">
        <strong>Error!</strong>
        <?=Tpl::out($this->error)?>
    </div>
</section>
<?php endif ?>

<?php if(!empty($this->success)): ?>
<section class="container">
    <div class="alert alert-info" style="margin:0;">
	    <strong>Success!</strong>
	    <?=Tpl::out($this->success)?>
    </div>
</section>
<?php endif ?>