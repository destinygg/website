<?php
use Destiny\Common\Utils\Date;
use Destiny\Common\Utils\Tpl;
?>
<section class="container">
    <div class="content-dark clearfix">
        <div class="ds-block">
            <h3><?= Tpl::out($this->user['username']) ?></h3>
            <div style="display: inline-block;">
                <p>
                    <span>Joined on <?=Tpl::moment(Date::getDateTime($this->user['createdDate']), 'jS F, Y H:i a', 'Do MMMM, YYYY h:m a')?></span><br />
                    Check out your old <a href="/profile/subscriptions" title="Your Subscriptions">Subscriptions</a>, <a href="/profile/gifts" title="Your Gifts">Gifts</a> and <a href="/profile/donations" title="Your Donations">Donations</a>.
                </p>
                <hr />
            </div>
            <div style="width: 100%; clear: both;">
                <a href="/logout" class="btn btn-danger">Sign Out</a>
            </div>
        </div>
    </div>
</section>