<?php
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Session\Session;
use Destiny\Common\User\UserRole;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?=Tpl::title($this->title)?>
    <?php include 'seg/meta.php' ?>
    <?=Tpl::manifestLink('web.css')?>
</head>
<body id="subscribe">
<div id="page-wrap">

    <?php include 'seg/nav.php' ?>

    <section class="container">
        <h1 class="title">
            <span>Subscribe</span>
            <small>let us strengthen our parasocial bond</small>
        </h1>

        <div class="row mt-5">
            <div class="col">
                <h3 class="text-center">Select a sub</h3>
            </div>
        </div>
        <div id="step-1-sub" class="row mt-1">
            <div class="col-xl-3 col-md-6 col-12 mb-4">
                <div class="sub-tier">
                    <h2 class="mb-0">Tier</h2>
                    <?php $romanNumeral = explode(' ', $this->tiers[0]['tierLabel'])[1] ?>
                    <p class="tier-numeral"><?= $romanNumeral ?></p>
                    <div class="perks">
                        <p>"Get access to chat features and be eligible for future subscriber events!" ―Destiny</p>
                        <ul>
                            <li>Exquisite chat <img class='tier-1-flair'></img> flair</li>
                            <li>A colorful <span class="colored-username t1">username</span></li>
                            <li>The ability to <span class="greentext">&gt;greentext</span></li>
                            <li>Subscriber role in Discord</li>
                            <li>Participate in viewer call-ins</li>
                            <li>Cast 2 votes in sub-weighted polls</li>
                            <li>Mercy from the mod team</li>
                            <li>Destiny will call you "buddy"</li>
                        </ul>
                    </div>
                    <div class="periods">
                        <?php $sub = $this->subscriptions['1-MONTH-SUB']; $price = floatval($sub['amount']); ?>
                        <div class="selectable selected" data-select-id="<?= $sub['id'] ?>" data-select-price="<?= $price ?>" data-select-group="sub-tier">
                            <h3>$<?= $price ?></h3>
                            <small><?= $sub['billingFrequency'] ?> <?= $sub['billingPeriod'] ?></small>
                        </div>
                        <?php $sub = $this->subscriptions['3-MONTH-SUB']; ?>
                        <div class="selectable" data-select-id="<?= $sub['id'] ?>" data-select-price="<?= floatval($sub['amount']) ?>" data-select-group="sub-tier">
                            <h3><strike>$<?= $price * $sub['billingFrequency'] ?></strike> $<?= floatval($sub['amount']) ?></h3>
                            <small><?= $sub['billingFrequency'] ?> <?= $sub['billingPeriod'] ?></small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 col-12 mb-4">
                <div class="sub-tier">
                    <h2 class="mb-0">Tier</h2>
                    <?php $romanNumeral = explode(' ', $this->tiers[1]['tierLabel'])[1] ?>
                    <p class="tier-numeral"><?= $romanNumeral ?></p>
                    <div class="perks">
                        <p>"Got a bit more to contribute? Probably the best investment of all time." ―Destiny</p>
                        <ul>
                            <li>Exquisite chat <img class='tier-2-flair'></img> flair</li>
                            <li>A colorful <span class="colored-username t2">username</span></li>
                            <li>The ability to <span class="greentext">&gt;greentext</span></li>
                            <li>Subscriber role in Discord</li>
                            <li>Priority during viewer call-ins</li>
                            <li>Cast 4 votes in sub-weighted polls</li>
                            <li>Even more mercy from mods</li>
                            <li>A firm handshake from Destiny</li>
                        </ul>
                    </div>
                    <div class="periods">
                        <?php $sub = $this->subscriptions['1-MONTH-SUB2']; $price = floatval($sub['amount']); ?>
                        <div class="selectable" data-select-id="<?= $sub['id'] ?>" data-select-price="<?= $price ?>" data-select-group="sub-tier">
                            <h3>$<?= $price ?></h3>
                            <small><?= $sub['billingFrequency'] ?> <?= $sub['billingPeriod'] ?></small>
                        </div>
                        <?php $sub = $this->subscriptions['3-MONTH-SUB2']; ?>
                        <div class="selectable" data-select-id="<?= $sub['id'] ?>" data-select-price="<?= floatval($sub['amount']) ?>" data-select-group="sub-tier">
                            <h3><strike>$<?= $price * $sub['billingFrequency'] ?></strike> $<?= floatval($sub['amount']) ?></h3>
                            <small><?= $sub['billingFrequency'] ?> <?= $sub['billingPeriod'] ?></small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 col-12 mb-4">
                <div class="sub-tier">
                    <h2 class="mb-0">Tier</h2>
                    <?php $romanNumeral = explode(' ', $this->tiers[2]['tierLabel'])[1] ?>
                    <p class="tier-numeral"><?= $romanNumeral ?></p>
                    <div class="perks">
                        <p>"Wow such value so prestige you should purchase immediately." ―Destiny</p>
                        <ul>
                            <li>Exquisite chat <img class='tier-3-flair'></img> flair</li>
                            <li>A colorful <span class="colored-username t3">username</span></li>
                            <li>The ability to <span class="greentext">&gt;greentext</span></li>
                            <li>Subscriber role in Discord</li>
                            <li>Priority during viewer call-ins</li>
                            <li>Cast 8 votes in sub-weighted polls</li>
                            <li>Mods will easily forgive your transgressions</li>
                            <li>Destiny will give you a consensual hug</li>
                        </ul>
                    </div>
                    <div class="periods">
                        <?php $sub = $this->subscriptions['1-MONTH-SUB3']; $price = floatval($sub['amount']); ?>
                        <div class="selectable" data-select-id="<?= $sub['id'] ?>" data-select-price="<?= $price ?>" data-select-group="sub-tier">
                            <h3>$<?= $price ?></h3>
                            <small><?= $sub['billingFrequency'] ?> <?= $sub['billingPeriod'] ?></small>
                        </div>
                        <?php $sub = $this->subscriptions['3-MONTH-SUB3']; ?>
                        <div class="selectable" data-select-id="<?= $sub['id'] ?>" data-select-price="<?= floatval($sub['amount']) ?>" data-select-group="sub-tier">
                            <h3><strike>$<?= $price * $sub['billingFrequency'] ?></strike> $<?= floatval($sub['amount']) ?></h3>
                            <small><?= $sub['billingFrequency'] ?> <?= $sub['billingPeriod'] ?></small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 col-12">
                <div class="sub-tier">
                    <h2 class="mb-0">Tier</h2>
                    <?php $romanNumeral = explode(' ', $this->tiers[3]['tierLabel'])[1] ?>
                    <p class="tier-numeral"><?= $romanNumeral ?></p>
                    <div class="perks">
                        <p>"Know in your heart you have made the right choice here." ―Destiny</p>
                        <ul>
                            <li>Exquisite chat <img class='tier-4-flair'></img> flair</li>
                            <li>A colorful <span class="colored-username t4">username</span></li>
                            <li>The ability to <span class="greentext">&gt;greentext</span></li>
                            <li>Subscriber role in Discord</li>
                            <li>Priority during viewer call-ins</li>
                            <li>Cast 16 votes in sub-weighted polls</li>
                            <li>Near-impenetrable ban armor</li>
                            <li>A photo with Destiny (no weird poses)</li>
                        </ul>
                    </div>
                    <div class="periods">
                        <?php $sub = $this->subscriptions['1-MONTH-SUB4']; $price = floatval($sub['amount']); ?>
                        <div class="selectable" data-select-id="<?= $sub['id'] ?>" data-select-price="<?= $price ?>" data-select-group="sub-tier">
                            <h3>$<?= $price ?></h3>
                            <small><?= $sub['billingFrequency'] ?> <?= $sub['billingPeriod'] ?></small>
                        </div>
                        <?php $sub = $this->subscriptions['3-MONTH-SUB4']; ?>
                        <div class="selectable" data-select-id="<?= $sub['id'] ?>" data-select-price="<?= floatval($sub['amount']) ?>" data-select-group="sub-tier">
                            <h3><strike>$<?= $price * $sub['billingFrequency'] ?></strike> $<?= floatval($sub['amount']) ?></h3>
                            <small><?= $sub['billingFrequency'] ?> <?= $sub['billingPeriod'] ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col">
                <h3 class="text-center">Select a recipient</h3>
            </div>
        </div>
        <div class="row d-flex align-items-start justify-content-center">
            <div class="col-auto mt-1">
                <div id="self" class="recipient">
                    <div class="selectable selected" data-select-id="self" data-select-group="recipient">
                        <i class="fas fa-smile fa-3x"></i>
                        <p>Please notice me, Senpai.<br>(This sub is for me.)</p>
                    </div>
                </div>
            </div>
            <div class="col-auto mt-1">
                <div id="direct-gift" class="recipient">
                    <div class="selectable" data-select-id="direct-gift" data-select-group="recipient">
                        <i class="fas fa-gift fa-3x"></i>
                        <p>Reward a friend for good memery.<br>(Gift this sub to <span class="value" data-giftee-username="">a specific user</span>.)</p>
                    </div>
                    <form id="search-user" style="display: none;">
                        <div class="form-group">
                            <label>Search for a user</label>
                            <div class="input-group mb-3">
                                <input type="text" id="username-input" class="form-control" placeholder="e.g., Destiny">
                                <div class="input-group-append">
                                    <button type="Submit" class="btn btn-outline-secondary btn-sm"><i class="fas fa-search px-2"></i></button>
                                </div>
                                <div class="valid-feedback"></div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <button class="btn btn-secondary btn-sm" disabled>Confirm</button>
                    </form>
                    <i class="fas fa-arrow-down expansion-arrow" data-expansion-target="form#search-user"></i>
                </div>
            </div>
            <div class="col-auto mt-1">
                <div id="mass-gift" class="recipient">
                    <div class="selectable" data-select-id="mass-gift" data-select-group="recipient">
                        <i class="fas fa-gifts fa-3x"></i>
                        <p>Any oil princes?<br>(Gift <span class="value" data-quantity>one or more subs</span> to random users.)</p>
                    </div>
                    <div id="quantity-selector" style="display: none;">
                        <label>How many subs?</label>
                        <div id="static-quantity-buttons" class="mb-3">
                            <div class="two-tone-button" data-quantity="1">
                                <div><p>1 Sub</p></div>
                                <div><p>$5</p></div>
                            </div>
                            <div class="two-tone-button" data-quantity="5">
                                <div><p>5 Subs</p></div>
                                <div><p>$25</p></div>
                            </div>
                            <div class="two-tone-button" data-quantity="10">
                                <div><p>10 Subs</p></div>
                                <div><p>$50</p></div>
                            </div>
                            <div class="two-tone-button" data-quantity="20">
                                <div><p>20 Subs</p></div>
                                <div><p>$100</p></div>
                            </div>
                            <div class="two-tone-button" data-quantity="50">
                                <div><p>50 Subs</p></div>
                                <div><p>$250</p></div>
                            </div>
                            <div class="two-tone-button" data-quantity="100">
                                <div><p>100 Subs</p></div>
                                <div><p>$500</p></div>
                            </div>
                        </div>
                        <label class="align-self-start">Custom amount</label>
                        <div id="custom-quantity-button">
                            <div class="form-group">
                                <input id="quantity" class="form-control" type="number" min="1" max="100" value="1">
                                <small class="form-text text-muted ml-1">Max 100</small>
                            </div>
                            <div class="two-tone-button" data-quantity="1">
                                <div><p>1 Sub</p></div>
                                <div><p>$5</p></div>
                            </div>
                        </div>
                    </div>
                    <i class="fas fa-arrow-down expansion-arrow" data-expansion-target="div#quantity-selector"></i>
                </div>
            </div>
        </div>

        <div class="row d-flex justify-content-center mt-5">
            <div class="col-auto">
                <form id="continue-form" class="d-flex flex-column align-items-center justify-content-center" action="/subscription/confirm" method="post">
                    <input type="hidden" name="subscriptionId" value>
                    <input type="hidden" name="purchaseType" value>
                    <input type="hidden" name="giftee" value>
                    <input type="hidden" name="quantity" value>
                    <button type="Submit" class="btn btn-primary btn-lg">Continue <i class="fas fa-arrow-right"></i></button>
                    <div class="invalid-feedback"></div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'seg/alerts.php' ?>
<?php include 'seg/foot.php' ?>
<?php include 'seg/tracker.php' ?>
<?=Tpl::manifestScript('runtime.js')?>
<?=Tpl::manifestScript('common.vendor.js')?>
<?=Tpl::manifestScript('web.js')?>

</body>
</html>