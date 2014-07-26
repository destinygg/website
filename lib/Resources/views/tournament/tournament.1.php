<?php
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
use Destiny\Common\User\UserRole;
use Destiny\Common\Session; 
?>
<!DOCTYPE html>
<html>
<head>
<title><?=$model->title?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/commontop.php') ?>
<?php include Tpl::file('seg/google.tracker.php') ?>
<link href="<?= Config::cdnv() ?>/tournament/css/tournament.min.css" rel="stylesheet" media="screen">
</head>
<body id="tournament">

    <div id="main-nav" class="navbar navbar-static-top">
        <div class="container">
            <a class="brand pull-left" href="/">Destiny.gg</a>
            <ul class="nav pull-right navbar-nav">
                <li class="active"><a title="Home" href="#tournament"><i class="icon-nav-home"></i> Home <i class="icon-nav-arrow-down"></i></a></li>
                <li><a title="About" href="#about">About <i class="icon-nav-arrow-down"></i></a></li>
                <li><a title="Brackets" href="#brackets">Youtube <i class="icon-nav-arrow-down"></i></a></li>
                <li><a title="Schedule" href="#t-dates">Schedule <i class="icon-nav-arrow-down"></i></a></li>
            </ul>
        </div>
    </div>

    <section id="t-scroller">
        <div class="container">

            <div id="slide1" class="t-scroller-slide">
                <div id="t-alpha-logo">Destiny.gg Tournament 1</div>
                <div id="t-alpha-buttons">
                    <a class="t-btn" href="#t-timer">More Information</a>
                    <a class="t-btn"  href="http://www.destiny.gg/bigscreen">View on Destiny.gg</a>
                </div>
            </div>

        </div>
    </section>

    <section id="t-timer">
        <div class="container">
            <div id="t-timer-day" class="t-timer-unit">
                <div class="t-number">24</div>
                <div class="t-label">DAYS</div>
            </div>
            <div id="t-timer-hour" class="t-timer-unit">
                <div class="t-number">08</div>
                <div class="t-label">HOURS</div>
            </div>
            <div id="t-timer-minute" class="t-timer-unit">
                <div class="t-number">34</div>
                <div class="t-label">MIN</div>
            </div>
            <div id="t-timer-second" class="t-timer-unit">
                <div class="t-number">12</div>
                <div class="t-label">SEC</div>
            </div>
        </div>
    </section>

    <section id="t-dates">
        <div class="container">
            <h1>Important Dates</h1>
            <div class="row">
                <div class="col-md-3">
                    <h2>Sunday 3 August</h2>
                    <h3>Close-off @11:59PM EDT Ladder Qualifier</h3>
                    <p>No signups, must be top 8 on the NA ladder before close-off date, players will be contacted by Destiny.</p>
                </div>
                <div class="col-md-3">
                    <h2>Tues-Fri 5-8 August</h2>
                    <h3>Starting @10:00AM EDT Group Stages</h3>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent semper semper posuere. Interdum et malesuada fames ac ante ipsum primis in faucibus. Integer faucibus blandit ante, eget tristique neque dignissim et. Morbi consectetur lectus eu congue auctor. Integer vitae lorem cursus arcu molestie elementum in at sem.</p>
                </div>
                <div class="col-md-3">
                    <h2>Saturday 9 August</h2>
                    <h3>Starting @10:00AM EDT Quarterfinals</h3>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent semper semper posuere. Interdum et malesuada fames ac ante ipsum primis in faucibus. Integer faucibus blandit ante, eget tristique neque dignissim et. Morbi consectetur lectus eu congue auctor. Integer vitae lorem cursus arcu molestie elementum in at sem.</p>
                </div>
                <div class="col-md-3">
                    <h2>Sunday 10 August</h2>
                    <h3>Starting @10:00AM EDT Semi/Finals</h3>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent semper semper posuere. Interdum et malesuada fames ac ante ipsum primis in faucibus. Integer faucibus blandit ante, eget tristique neque dignissim et. Morbi consectetur lectus eu congue auctor. Integer vitae lorem cursus arcu molestie elementum in at sem.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="t-players">
        <div class="container">
            <h1>Invited Players</h1>
            <div class="row">

                <div id="hero" class="t-person col-md-3 col-sm-4">
                    <div class="t-portrait t-portrait-hero"></div>
                    <div class="t-name">Liquid’HerO</div>
                    <div class="t-social">
                        <a class="icon-protoss" title="Protoss" href="#"></a>
                        <a class="icon-twitch" title="Twitch" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="#"></a>
                    </div>
                </div>

                <div id="innovation" class="t-person col-md-3 col-sm-4">
                    <div class="t-portrait t-portrait-innovation"></div>
                    <div class="t-name">Acer INnoVation</div>
                    <div class="t-social">
                        <a class="icon-terran" title="Terran" href="#"></a>
                        <a class="icon-twitch" title="Twitch" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="#"></a>
                    </div>
                </div>

                <div id="life" class="t-person col-md-3 col-sm-4">
                    <div class="t-portrait t-portrait-life"></div>
                    <div class="t-name">StarTale Life</div>
                    <div class="t-social">
                        <a class="icon-zerg" title="Zerg" href="#"></a>
                        <a class="icon-twitch" title="Twitch" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="#"></a>
                    </div>
                </div>

                <div id="mc" class="t-person col-md-3 col-sm-4">
                    <div class="t-portrait t-portrait-mc"></div>
                    <div class="t-name">MC</div>
                    <div class="t-social">
                        <a class="icon-protoss" title="Protoss" href="#"></a>
                        <a class="icon-twitch" title="Twitch" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="#"></a>
                    </div>
                </div>

                <div id="polt" class="t-person col-md-3 col-sm-4">
                    <div class="t-portrait t-portrait-polt"></div>
                    <div class="t-name">CM Storm Polt</div>
                    <div class="t-social">
                        <a class="icon-terran" title="Terran" href="#"></a>
                        <a class="icon-twitch" title="Twitch" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="#"></a>
                    </div>
                </div>

                <div id="scarlett" class="t-person col-md-3 col-sm-4">
                    <div class="t-portrait t-portrait-scarlett"></div>
                    <div class="t-name">Acer Scarlett</div>
                    <div class="t-social">
                        <a class="icon-zerg" title="Zerg" href="#"></a>
                        <a class="icon-twitch" title="Twitch" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="#"></a>
                    </div>
                </div>

                <div id="snute" class="t-person col-md-3 col-md-offset-0 col-sm-4 col-sm-offset-2">
                    <div class="t-portrait t-portrait-snute"></div>
                    <div class="t-name">Liquid’Snute</div>
                    <div class="t-social">
                        <a class="icon-zerg" title="Zerg" href="#"></a>
                        <a class="icon-twitch" title="Twitch" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="#"></a>
                    </div>
                </div>

                <div id="violet" class="t-person col-md-3 col-sm-4">
                    <div class="t-portrait t-portrait-violet"></div>
                    <div class="t-name">viOLet</div>
                    <div class="t-social">
                        <a class="icon-terran" title="Terran" href="#"></a>
                        <a class="icon-twitch" title="Twitch" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="#"></a>
                    </div>
                </div>
            
            </div>

        </div>

    </section>

    <section id="t-casters">
        <div class="container">
            <h1>Casters</h1>

            <div class="row">

                <div id="incontrol" class="t-person col-md-3 col-sm-4">
                    <div class="t-portrait t-portrait-incontrol"></div>
                    <div class="t-name">iNcontroL</div>
                    <div class="t-social">
                        <a class="icon-twitch" title="Twitch" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="#"></a>
                    </div>
                </div>

                <div id="minigun" class="t-person col-md-3 col-sm-4">
                    <div class="t-portrait t-portrait-minigun"></div>
                    <div class="t-name">Minigun</div>
                    <div class="t-social">
                        <a class="icon-twitch" title="Twitch" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="#"></a>
                    </div>
                </div>

                <div id="nathanias" class="t-person col-md-3 col-sm-4">
                    <div class="t-portrait t-portrait-nathanias"></div>
                    <div class="t-name">Nathanias</div>
                    <div class="t-social">
                        <a class="icon-twitch" title="Twitch" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="#"></a>
                    </div>
                </div>

                <div id="rotterdam" class="t-person col-md-3 col-sm-4">
                    <div class="t-portrait t-portrait-rotterdam"></div>
                    <div class="t-name">RotterdaM</div>
                    <div class="t-social">
                        <a class="icon-twitch" title="Twitch" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="#"></a>
                    </div>
                </div>

                <div id="tod" class="t-person col-md-3 col-md-offset-3 col-sm-4 col-sm-offset-0">
                    <div class="t-portrait t-portrait-tod"></div>
                    <div class="t-name">Tod</div>
                    <div class="t-social">
                        <a class="icon-twitch" title="Twitch" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="#"></a>
                    </div>
                </div>

                <div id="tb" class="t-person col-md-3 col-sm-4">
                    <div class="t-portrait t-portrait-tb"></div>
                    <div class="t-name">TotalBiscuit</div>
                    <div class="t-social">
                        <a class="icon-twitch" title="Twitch" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="#"></a>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <section id="t-foot">
        <footer class="container clearfix">
            <p class="pull-left">
                <span><?=Config::$a['meta']['shortName']?> &copy; <?=date('Y')?> </span>
                <span><a href="mailto:steven.bonnell.ii@gmail.com" title="Email Destiny">@Contact</a></span>
            </p>
            <p class="pull-right" style="text-align: right;">
                Source code for <a href="https://github.com/destinygg/website">website</a> and <a href="https://github.com/destinygg/chat">chat</a> @ <a href="https://github.com/destinygg">Github</a>
            </p>
        </footer>
    </section>

    <?php include Tpl::file('seg/commonbottom.php') ?>
    
</body>
</html>