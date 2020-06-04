<?php
use Destiny\Common\Config;
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
                                <option <?=($role['roleId'] == $this->role) ? 'selected':''?> value="<?=Tpl::out($role['roleId'])?>"><?=Tpl::out($role['roleLabel'])?></option>
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

                        <div class="dropdown mr-3">
                            <button type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown"></button>
                            <div class="dropdown-menu">
                                <a id="ban-users-btn" class="dropdown-item" href="#">Ban</a>
                                <a id="delete-users-btn" class="dropdown-item" href="#">Delete</a>
                            </div>
                        </div>

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
                                <label for="gridSize" class="text-muted">Showing (<?=Tpl::number(count($this->users['list']))?> of <?=Tpl::number($this->users['total'])?>)</label>
                                <select id="gridSize" name="size" class="form-control ml-2">
                                    <?php foreach ($this->sizes as $size): ?>
                                        <option value="<?=$size?>" <?= $this->size == $size ? " selected":""?>><?=$size?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                    </div>

                    <table id="users-table" class="grid" data-sort="<?=Tpl::out($this->sort)?>" data-order="<?=Tpl::out($this->order)?>">
                        <thead>
                            <tr>
                                <td class="selector"><i class="far fa-circle"></i></td>
                                <td style="width: 300px;" data-sort="username">User</td>
                                <td data-sort="status">Status</td>
                                <td data-sort="id">Created on</td>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($this->users['list'] as $user): ?>
                            <tr>
                                <td data-id="<?=$user['userId']?>" class="selector"><i class="far fa-circle"></i></td>
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

<div class="modal fade" id="delete-users-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="/admin/users/delete" method="post">
                <div class="modal-body">
                    <p class="modal-message" style="color: #797979;">Delete some users?</p>
                    <div class="g-recaptcha" data-sitekey="<?=Tpl::out(Config::$a['g-recaptcha']['key'])?>"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary mr-auto" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="ban-users-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="/admin/users/ban" method="post">
                <div class="modal-body">
                    <p class="modal-message" style="color: #797979;">Ban some users?</p>
                    <div class="mt-3">
                        <div class="form-group">
                            <input class="form-control" type="text" name="reason" value="Mass ban" placeholder="Reason ..." />
                        </div>
                        <div class="form-group">
                            <select class="form-control" name="duration">
                                <option value="600">10 minutes</option>
                                <option value="1800">30 minutes</option>
                                <option value="3600" selected>1 hours</option>
                                <option value="21600">6 hours</option>
                                <option value="86400">1 day</option>
                                <option value="108000">30 day</option>
                                <option value="">Permanent</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary mr-auto" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Ban</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'seg/alerts.php' ?>
<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<?=Tpl::manifestScript('runtime.js')?>
<?=Tpl::manifestScript('common.vendor.js')?>
<?=Tpl::manifestScript('web.js')?>
<?=Tpl::manifestScript('admin.js')?>
<script src="https://www.google.com/recaptcha/api.js"></script>

</body>
</html>