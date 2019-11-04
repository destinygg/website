<?php
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Tpl;
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
        <form id="subscriber-filter" class="filter-form" role="form">
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
                        <label class="mr-2">Tier</label>
                        <select name="tier" class="form-control">
                            <option value="" <?=(!$this->tier ? 'selected':'')?>>Any</option>
                            <?php foreach ($this->tiers as $tier): ?>
                                <option <?=($tier['tier'] == $this->tier) ? 'selected':''?> value="<?=Tpl::out($tier['tier'])?>"><?=Tpl::out($tier['tierLabel'])?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="mr-2">Recurring</label>
                        <select name="recurring" class="form-control">
                            <option value="" <?=($this->recurring == '' ? 'selected':'')?>>Any</option>
                            <option value="1" <?=($this->recurring === '1' ? 'selected':'')?>>Yes</option>
                            <option value="0" <?=($this->recurring === '0' ? 'selected':'')?>>No</option>
                        </select>
                    </div>
                    <div class="filter-buttons">
                        <button type="reset" class="btn btn-secondary btn-dark">Reset</button>
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </div>
            </div>

            <div class="content content-dark clearfix">
                <div data-size="<?=Tpl::out($this->size)?>" data-page="<?=Tpl::out($this->page)?>" class="stream stream-grid" style="width: 100%;">

                    <div class="ds-block" style="display: flex;">
                        <input type="hidden" name="page" value="1" />

                        <div class="form-inline" role="form" style="flex: 1;">
                            <?php if($this->subscriptions['totalpages'] > 1): ?>
                                <ul class="pagination" style="margin: 0 15px 0 0;">
                                    <li class="page-item">
                                        <a class="page-link" data-page="1" href="?page=0">First</a>
                                    </li>
                                    <?php for($i = max(1, $this->subscriptions['page'] - 2); $i <= min($this->subscriptions['page'] + 2, $this->subscriptions['totalpages']); $i++): ?>
                                        <li class="page-item <?=($this->subscriptions['page'] == $i) ? 'active':''?>">
                                            <a class="page-link" data-page="<?=$i?>" href="?page=<?=$i?>"><?=$i?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item">
                                        <a class="page-link" data-page="<?=$this->subscriptions['totalpages']?>" href="?page=<?=$this->subscriptions['totalpages']?>">Last</a>
                                    </li>
                                </ul>
                            <?php endif ?>
                        </div>

                        <div class="form-inline" role="form">
                            <div class="form-group">
                                <label for="gridSize" class="text-muted">Showing (<?=count($this->subscriptions['list'])?>)</label>
                                <select id="gridSize" name="size" class="form-control ml-2">
                                    <?php foreach ($this->sizes as $size): ?>
                                        <option value="<?=$size?>"><?=$size?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                    </div>

                    <table class="grid">
                        <thead>
                            <tr>
                                <td style="width: 300px;">User</td>
                                <td>Tier</td>
                                <td>Status</td>
                                <td>Recurring</td>
                                <td>Created on</td>
                                <td>Ends on</td>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($this->subscriptions['list'] as $sub): ?>
                            <tr>
                                <td>
                                    <a href="/admin/user/<?=$sub['userId']?>/subscription/<?=$sub['subscriptionId']?>/edit"><?=Tpl::out($sub['username'])?></a>
                                    <?php if(!empty($sub['gifter'])): ?>
                                    &nbsp;(<a title="Gifted by" href="/admin/user/<?=$sub['gifter']?>/edit"><i class="fas fa-gift"></i> <?=Tpl::out($sub['gifterUsername'])?></a>)
                                    <?php endif ?>
                                </td>
                                <td><?=Tpl::out($sub['type']['tierLabel'])?></td>
                                <td><?=Tpl::out($sub['status'])?></td>
                                <td><?=($sub['recurring'] == 1) ? 'Yes':'No'?></td>
                                <td><?=Tpl::moment(Date::getDateTime($sub['createdDate']), Date::STRING_FORMAT)?></td>
                                <td><?=Tpl::moment(Date::getDateTime($sub['endDate']), Date::STRING_FORMAT)?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </form>
    </section>

</div>

<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<?=Tpl::manifestScript('runtime.js')?>
<?=Tpl::manifestScript('common.vendor.js')?>
<?=Tpl::manifestScript('web.js')?>
<?=Tpl::manifestScript('admin.js')?>

</body>
</html>