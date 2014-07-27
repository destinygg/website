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
                <li class="active"><a title="Home" href="/"><i class="icon-nav-home"></i> Home <i class="icon-nav-arrow-down"></i></a></li>
                <li><a title="About" href="#t-about">About <i class="icon-nav-arrow-down"></i></a></li>
                <li><a title="Brackets" href="#t-brackets">Brackets <i class="icon-nav-arrow-down"></i></a></li>
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
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/Liquid_HerO" target="_blank"></a>
                    </div>
                </div>

                <div id="innovation" class="t-person col-md-3 col-sm-4">
                    <div class="t-portrait t-portrait-innovation"></div>
                    <div class="t-name">Acer INnoVation</div>
                    <div class="t-social">
                        <a class="icon-terran" title="Terran" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/AcerINnoVation" target="_blank"></a>
                    </div>
                </div>

                <div id="life" class="t-person col-md-3 col-sm-4">
                    <div class="t-portrait t-portrait-life"></div>
                    <div class="t-name">StarTale Life</div>
                    <div class="t-social">
                        <a class="icon-zerg" title="Zerg" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/Startale_Life" target="_blank"></a>
                    </div>
                </div>

                <div id="mc" class="t-person col-md-3 col-sm-4">
                    <div class="t-portrait t-portrait-mc"></div>
                    <div class="t-name">MC</div>
                    <div class="t-social">
                        <a class="icon-protoss" title="Protoss" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/MCtoss2" target="_blank"></a>
                    </div>
                </div>

                <div id="polt" class="t-person col-md-3 col-sm-4">
                    <div class="t-portrait t-portrait-polt"></div>
                    <div class="t-name">CM Storm Polt</div>
                    <div class="t-social">
                        <a class="icon-terran" title="Terran" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/CMStormPolt" target="_blank"></a>
                    </div>
                </div>

                <div id="scarlett" class="t-person col-md-3 col-sm-4">
                    <div class="t-portrait t-portrait-scarlett"></div>
                    <div class="t-name">Acer Scarlett</div>
                    <div class="t-social">
                        <a class="icon-zerg" title="Zerg" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/acerscarlett" target="_blank"></a>
                    </div>
                </div>

                <div id="snute" class="t-person col-md-3 col-md-offset-0 col-sm-4 col-sm-offset-2">
                    <div class="t-portrait t-portrait-snute"></div>
                    <div class="t-name">Liquid’Snute</div>
                    <div class="t-social">
                        <a class="icon-zerg" title="Zerg" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/LiquidSnute" target="_blank"></a>
                    </div>
                </div>

                <div id="violet" class="t-person col-md-3 col-sm-4">
                    <div class="t-portrait t-portrait-violet"></div>
                    <div class="t-name">viOLet</div>
                    <div class="t-social">
                        <a class="icon-terran" title="Terran" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/viOLetstarcraft" target="_blank"></a>
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
                        <a class="icon-protoss" title="Protoss" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/EGiNcontroL" target="_blank"></a>
                    </div>
                </div>

                <div id="minigun" class="t-person col-md-3 col-sm-4">
                    <div class="t-portrait t-portrait-minigun"></div>
                    <div class="t-name">Minigun</div>
                    <div class="t-social">
                        <a class="icon-protoss" title="Protoss" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/ROOT_Minigun" target="_blank"></a>
                    </div>
                </div>

                <div id="nathanias" class="t-person col-md-3 col-sm-4">
                    <div class="t-portrait t-portrait-nathanias"></div>
                    <div class="t-name">Nathanias</div>
                    <div class="t-social">
                        <a class="icon-terran" title="Terran" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/nathaniastv" target="_blank"></a>
                    </div>
                </div>

                <div id="rotterdam" class="t-person col-md-3 col-sm-4">
                    <div class="t-portrait t-portrait-rotterdam"></div>
                    <div class="t-name">RotterdaM</div>
                    <div class="t-social">
                        <a class="icon-protoss" title="Protoss" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/RotterdaM08" target="_blank"></a>
                    </div>
                </div>

                <div id="tod" class="t-person col-md-3 col-md-offset-3 col-sm-4 col-sm-offset-0">
                    <div class="t-portrait t-portrait-tod"></div>
                    <div class="t-name">Tod</div>
                    <div class="t-social">
                        <a class="icon-protoss" title="Protoss" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/XMGToD" target="_blank"></a>
                    </div>
                </div>

                <div id="tb" class="t-person col-md-3 col-sm-4">
                    <div class="t-portrait t-portrait-tb"></div>
                    <div class="t-name">TotalBiscuit</div>
                    <div class="t-social">
                        <a class="icon-terran" title="Terran" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/Totalbiscuit" target="_blank"></a>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <section id="t-sponsors">
        <div class="container">
            <h1>sponsors</h1>

            <div class="row">

                <div id="jordwoodwatches" class="t-sponsor col-md-3">
                    <a class="t-logo sponsor-jordwoodwatches" href="http://www.woodwatches.com/" target="_blank" title="www.woodwatches.com"></a>
                    <div class="t-name">Wood Watches</div>
                    <div class="t-sponsor-link"><a href="http://www.woodwatches.com/" target="_blank" title="www.woodwatches.com">www.woodwatches.com</a></div>
                    <p>It's about time someone delivered an unconventional answer to age-old wrist candy. JORD watches are designed to take people back to nature and away from today's metal &amp; rubber. We want to challenge the norm by making unique time pieces as a focal point for everyday fashion.</p>
                </div>

                <div id="breakingoutinvitational" class="t-sponsor col-md-3">
                    <a class="t-logo sponsor-breakingoutinvitational" href="http://www.teamliquid.net/forum/sc2-tournaments/449573-the-breakout-invitational-2-north-america" target="_blank" title="www.teamliquid.net"></a>
                    <div class="t-name">Breaking Out</div>
                    <div class="t-sponsor-link"><a href="http://www.teamliquid.net/forum/sc2-tournaments/449573-the-breakout-invitational-2-north-america" target="_blank" title="www.teamliquid.net">www.teamliquid.net</a></div>
                    <p>Breaking Out is a show about up &amp; coming North American Starcraft 2 players. The show aims to highlight the next "breakout" players while they're still making a name for themself through coverage of their games, some interviews, and a little bit of fun.</p>
                </div>

                <div id="videogamevotersnetwork" class="t-sponsor col-md-3">
                    <a class="t-logo sponsor-videogamevotersnetwork" href="http://videogamevoters.org/" target="_blank" title="videogamevoters.org"></a>
                    <div class="t-name">Video Game Voters</div>
                    <div class="t-sponsor-link"><a href="http://videogamevoters.org/" target="_blank" title="videogamevoters.org">videogamevoters.org</a></div>
                    <p>The Video Game Voters Network is a place for American gamers to organize and defend against threats to video games by registering to vote and letting Congress know how important this issue is to the community.</p>
                </div>

                <div id="letskungfu" class="t-sponsor col-md-3">
                    <a class="t-logo sponsor-letskungfu" href="http://www.teamliquid.net/forum/starcraft-2/370530-show-lets-kung-fu" target="_blank" title="www.teamliquid.net"></a>
                    <div class="t-name">LetsKungFu</div>
                    <div class="t-sponsor-link"><a href="http://www.teamliquid.net/forum/starcraft-2/370530-show-lets-kung-fu" target="_blank" title="www.teamliquid.net">www.teamliquid.net</a></div>
                    <p>Web TV Series, focusing on eSports (StarCraft, League of Legends, etc)</p>
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