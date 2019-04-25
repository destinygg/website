<?php if(!empty($this->error) || !empty($this->success) || !empty($this->warning)): ?>
<section class="container">
    <?php include "alerts.php" ?>
</section>
<?php endif; ?>