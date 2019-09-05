<?php

use Destiny\Common\User\UserFeature;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Date;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?=Tpl::title($this->title)?>
    <?php include 'seg/meta.php' ?>
    <?=Tpl::manifestLink('web.css')?>
</head>
<body id="admin" class="no-contain">
<div id="page-wrap">

    <?php include 'seg/nav.php' ?>
    <?php include 'seg/admin.nav.php' ?>

    <section class="container">
        <form class="filter-form" role="form">

            <input type="hidden" name="page" value="1" />
            <input type="hidden" name="sort" value="<?=Tpl::out($this->sort)?>" />
            <input type="hidden" name="order" value="<?=Tpl::out($this->order)?>" />

            <div class="content content-dark mb-3">
                <div class="form-inline filters">
                    <div id="search-input" class="form-group">
                        <input name="search" type="text" class="form-control" style="width: 100%;" placeholder="Username or email..." value="<?=Tpl::out($this->search)?>" />
                    </div>
                    <div class="form-group">
                        <label class="mr-2">Status</label>
                        <select name="status" class="form-control">
                            <option value="" <?=(!$this->status ? 'selected':'')?>>Any</option>
                            <?php foreach ($this->statuses as $status): ?>
                                <option <?=($status == $this->status) ? 'selected':''?> value="<?=Tpl::out($status)?>"><?=Tpl::out($status)?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="mr-2">Role</label>
                        <select name="role" class="form-control">
                            <option value="" <?=(!$this->role ? 'selected':'')?>>Any</option>
                            <?php foreach ($this->roles as $role): ?>
                                <option <?=($role['roleName'] == $this->role) ? 'selected':''?> value="<?=Tpl::out($role['roleName'])?>"><?=Tpl::out($role['roleLabel'])?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="mr-2">Feature</label>
                        <select name="feature" class="form-control">
                            <option value="" <?=(!$this->feature ? 'selected':'')?>>Any</option>
                            <?php foreach ($this->features as $feature): ?>
                                <?php if(!in_array($feature['featureName'], UserFeature::$UNASSIGNABLE)): ?>
                                <option <?=($feature['featureId'] == $this->feature) ? 'selected':''?> value="<?=Tpl::out($feature['featureId'])?>"><?=Tpl::out($feature['featureLabel'])?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-buttons">
                        <button type="reset" class="btn btn-secondary btn-dark">Reset</button>
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </div>
            </div>

            <div class="content content-dark clearfix">
                <div data-size="<?=Tpl::out($this->size)?>" data-page="<?=Tpl::out($this->page)?>" class="stream stream-grid" style="width:100%;">

                    <div class="ds-block" style="display: flex;">

                        <div class="form-inline" role="form" style="flex: 1;">
                            <?php if($this->users['totalpages'] > 1): ?>
                            <ul class="pagination" style="margin: 0 15px 0 0;">
                                <li class="page-item">
                                    <a class="page-link" data-page="1" href="?page=0">First</a>
                                </li>
                                <?php for($i = max(1, $this->users['page'] - 2); $i <= min($this->users['page'] + 2, $this->users['totalpages']); $i++): ?>
                                    <li class="page-item <?=($this->users['page'] == $i) ? 'active':''?>">
                                        <a class="page-link" data-page="<?=$i?>" href="?page=<?=$i?>"><?=$i?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item">
                                    <a class="page-link" data-page="<?=$this->users['totalpages']?>" href="?page=<?=$this->users['totalpages']?>">Last</a>
                                </li>
                            </ul>
                            <?php endif ?>
                        </div>

                        <div class="form-inline" role="form">
                            <div class="form-group">
                                <label for="gridSize" class="text-muted">Showing (<?=count($this->users['list'])?>)</label>
                                <select id="gridSize" name="size" class="form-control ml-2">
                                    <?php foreach ($this->sizes as $size): ?>
                                        <option value="<?=$size?>"><?=$size?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                    </div>

                    <table class="grid" data-sort="<?=Tpl::out($this->sort)?>" data-order="<?=Tpl::out($this->order)?>">
                        <thead>
                        <tr>
                            <td data-sort="username">User <small>(<?=$this->users['total']?>)</small></td>
                            <td data-sort="status">Status</td>
                            <td data-sort="id">Created on</td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach($this->users['list'] as $user): ?>
                            <tr>
                                <td>
                                    <a href="/admin/user/<?=$user['userId']?>/edit"><?=Tpl::out($user['username'])?></a>
                                    <?php if(!empty($user['email'])): ?>(<?=Tpl::out($user['email'])?>)<?php endif; ?>
                                </td>
                                <td><?=$user['userStatus']?></td>
                                <td><?=Tpl::moment(Date::getDateTime($user['createdDate']), Date::STRING_FORMAT)?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                </div>
            </div>

        </form>
    </section>
</div>

<?php include 'seg/alerts.php' ?>
<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<?=Tpl::manifestScript('runtime.js')?>
<?=Tpl::manifestScript('common.vendor.js')?>
<?=Tpl::manifestScript('web.js')?>
<?=Tpl::manifestScript('admin.js')?>

</body>
</html>