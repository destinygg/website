<div id="alerts-container"></div>

<?php if(!empty($model->error)): ?>
<section class="container">
    <div class="alert alert-error" style="margin:0;">
        <strong>Error!</strong>
        <?=Tpl::out($model->error)?>
    </div>
</section>
<?php endif; ?>

<?php if(!empty($model->success)): ?>
<section class="container">
    <div class="alert alert-info" style="margin:0;">
	    <strong>Success!</strong>
	    <?=Tpl::out($model->success)?>
    </div>
</section>
<?php endif; ?>