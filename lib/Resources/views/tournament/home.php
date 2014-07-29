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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="<?=Config::cdn()?>/i.favicon.png">
<link href="<?=Config::cdn()?>/vendor/bootstrap-3.1.1/css/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdnv()?>/web/css/style.min.css" rel="stylesheet" media="screen">
<link href="<?=Config::cdnv()?>/tournament/css/tournament.min.css" rel="stylesheet" media="screen">
<?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body id="tournament">

    <a name="top"></a>
    <div id="main-nav" class="navbar navbar-static-top">
        <div class="container">
            <a class="brand pull-left" href="#top">Destiny.gg</a>
            <ul class="nav pull-right navbar-nav">
                <li class="active"><a title="Home" href="#top"><i class="icon-nav-home"></i> HOME <i class="icon-nav-arrow-down"></i></a></li>
                <li><a title="Important Dates" href="#dates">DATES <i class="icon-nav-arrow-down"></i></a></li>
                <li><a title="Invited Players" href="#players">PLAYERS <i class="icon-nav-arrow-down"></i></a></li>
                <li><a title="Casters" href="#casters">CASTERS <i class="icon-nav-arrow-down"></i></a></li>
                <li><a title="Brackets" href="#brackets">BRACKETS <i class="icon-nav-arrow-down"></i></a></li>
                <li><a title="Sponsers" href="#sponsors">SPONSORS <i class="icon-nav-arrow-down"></i></a></li>
            </ul>
        </div>
    </div>

    <section id="scroller">
        <div class="container">

            <div id="slide1" class="t-scroller-slide">
                <div id="t-alpha-logo">Destiny.gg Tournament 1</div>
                <div id="t-alpha-buttons">
                    <a class="t-btn" href="#dates">More Information</a>
                    <a class="t-btn" href="http://www.destiny.gg/bigscreen">View on Destiny.gg</a>
                </div>
            </div>

        </div>
    </section>

    <section id="timer">
        <div class="container">
            <div id="t-timer-day" class="t-timer-unit">
                <div class="t-number">00</div>
                <div class="t-label">DAYS</div>
            </div>
            <div id="t-timer-hour" class="t-timer-unit">
                <div class="t-number">00</div>
                <div class="t-label">HOURS</div>
            </div>
            <div id="t-timer-minute" class="t-timer-unit">
                <div class="t-number">00</div>
                <div class="t-label">MIN</div>
            </div>
            <div id="t-timer-second" class="t-timer-unit">
                <div class="t-number">00</div>
                <div class="t-label">SEC</div>
            </div>
        </div>
    </section>



    <section id="dates">
        <div class="container">
            <h1>Important Dates</h1>
            <div class="row">
                <div class="col-md-3">
                    <h2>Sunday 3 August</h2>
                    <h3>Closing @11:59 PM EDT</h3>
                    <p>The 8 qualified players from the ladder will be selected at this time. I will go through and make sure no one name-changed from a barcode account and verify that the games played were legitimate games.</p>
                </div>
                <div class="col-md-3">
                    <h2>Tues-Fri 5-8 August</h2>
                    <h3>Starting @10:00AM EDT</h3>
                    <ul class="dates-list">
                        <li><strong>Tues 5th</strong> Incontrol &amp; Destiny</li>
                        <li><strong>Wed 6th</strong> Minigun &amp; Destiny</li>
                        <li><strong>Thurs 7th</strong> Nathanias &amp; Destiny <i title="Pending confirmation" class="glyphicon glyphicon-time"></i></li>
                        <li><strong>Fri 8th</strong> Rotterdam &amp; Destiny <i title="Pending confirmation" class="glyphicon glyphicon-time"></i></li>
                    </ul>
                    <p>Group stages will start here. They will be played in a round robin style, with 5 best of 3's being played and a possible extra match if there is a three-way tie.</p>
                </div>
                <div class="col-md-3">
                    <h2>Saturday 9 August</h2>
                    <h3>Starting @10:00AM EDT</h3>
                    <ul class="dates-list">
                        <li>Totalbiscuit &amp; Destiny</li>
                    </ul>
                    <p>Quarter-finals.</p>
                </div>
                <div class="col-md-3">
                    <h2>Sunday 10 August</h2>
                    <h3>Starting @10:00AM EDT</h3>
                    <ul class="dates-list">
                        <li>Tod &amp; Destiny</li>
                    </ul>
                    <p>Semifinals and finals.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="players">
        <div class="container">
            <h1>Invited Players</h1>
            <div class="row">

                <div id="hero" class="t-person col-md-3 col-sm-4">
                    <a href="http://wiki.teamliquid.net/starcraft2/HerO" target="_blank" class="t-portrait t-portrait-hero"></a>
                    <div class="t-name">Liquid’HerO</div>
                    <div class="t-social">
                        <a class="icon-protoss" title="Protoss" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/Liquid_HerO" target="_blank"></a>
                    </div>
                </div>

                <div id="innovation" class="t-person col-md-3 col-sm-4">
                    <a href="http://wiki.teamliquid.net/starcraft2/INnoVation" target="_blank" class="t-portrait t-portrait-innovation"></a>
                    <div class="t-name">Acer INnoVation</div>
                    <div class="t-social">
                        <a class="icon-terran" title="Terran" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/AcerINnoVation" target="_blank"></a>
                    </div>
                </div>

                <div id="life" class="t-person col-md-3 col-sm-4">
                    <a href="http://wiki.teamliquid.net/starcraft2/Life" target="_blank" class="t-portrait t-portrait-life"></a>
                    <div class="t-name">StarTale Life</div>
                    <div class="t-social">
                        <a class="icon-zerg" title="Zerg" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/Startale_Life" target="_blank"></a>
                    </div>
                </div>

                <div id="mc" class="t-person col-md-3 col-sm-4">
                    <a href="http://wiki.teamliquid.net/starcraft2/MC" target="_blank" class="t-portrait t-portrait-mc"></a>
                    <div class="t-name">MC</div>
                    <div class="t-social">
                        <a class="icon-protoss" title="Protoss" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/MCtoss2" target="_blank"></a>
                    </div>
                </div>

                <div id="polt" class="t-person col-md-3 col-sm-4">
                    <a href="http://wiki.teamliquid.net/starcraft2/Polt" target="_blank" class="t-portrait t-portrait-polt"></a>
                    <div class="t-name">CM Storm Polt</div>
                    <div class="t-social">
                        <a class="icon-terran" title="Terran" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/CMStormPolt" target="_blank"></a>
                    </div>
                </div>

                <div id="scarlett" class="t-person col-md-3 col-sm-4">
                    <a href="http://wiki.teamliquid.net/starcraft2/Scarlett" target="_blank" class="t-portrait t-portrait-scarlett"></a>
                    <div class="t-name">Acer Scarlett</div>
                    <div class="t-social">
                        <a class="icon-zerg" title="Zerg" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/acerscarlett" target="_blank"></a>
                    </div>
                </div>

                <div id="snute" class="t-person col-md-3 col-md-offset-0 col-sm-4 col-sm-offset-2">
                    <a href="http://wiki.teamliquid.net/starcraft2/Snute" target="_blank" class="t-portrait t-portrait-snute"></a>
                    <div class="t-name">Liquid’Snute</div>
                    <div class="t-social">
                        <a class="icon-zerg" title="Zerg" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/LiquidSnute" target="_blank"></a>
                    </div>
                </div>

                <div id="violet" class="t-person col-md-3 col-sm-4">
                    <a href="http://wiki.teamliquid.net/starcraft2/ViOLet" target="_blank" class="t-portrait t-portrait-violet"></a>
                    <div class="t-name">viOLet</div>
                    <div class="t-social">
                        <a class="icon-terran" title="Terran" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/viOLetstarcraft" target="_blank"></a>
                    </div>
                </div>
            
            </div>

        </div>

    </section>

    <section id="casters">
        <div class="container">
            <h1>Casters</h1>

            <div class="row">

                <div id="incontrol" class="t-person col-md-3 col-sm-4">
                    <a href="http://wiki.teamliquid.net/starcraft2/INcontroL" target="_blank" class="t-portrait t-portrait-incontrol"></a>
                    <div class="t-name">iNcontroL</div>
                    <div class="t-social">
                        <a class="icon-protoss" title="Protoss" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/EGiNcontroL" target="_blank"></a>
                    </div>
                </div>

                <div id="minigun" class="t-person col-md-3 col-sm-4">
                    <a href="http://wiki.teamliquid.net/starcraft2/Minigun" target="_blank" class="t-portrait t-portrait-minigun"></a>
                    <div class="t-name">Minigun</div>
                    <div class="t-social">
                        <a class="icon-protoss" title="Protoss" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/ROOT_Minigun" target="_blank"></a>
                    </div>
                </div>

                <div id="nathanias" class="t-person col-md-3 col-sm-4">
                    <a href="http://wiki.teamliquid.net/starcraft2/Nathanias" target="_blank" class="t-portrait t-portrait-nathanias"></a>
                    <div class="t-name">Nathanias</div>
                    <div class="t-social">
                        <a class="icon-terran" title="Terran" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/nathaniastv" target="_blank"></a>
                    </div>
                </div>

                <div id="rotterdam" class="t-person col-md-3 col-sm-4">
                    <a href="http://wiki.teamliquid.net/starcraft2/RotterdaM" target="_blank" class="t-portrait t-portrait-rotterdam"></a>
                    <div class="t-name">RotterdaM</div>
                    <div class="t-social">
                        <a class="icon-protoss" title="Protoss" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/RotterdaM08" target="_blank"></a>
                    </div>
                </div>

                <div id="tod" class="t-person col-md-3 col-md-offset-3 col-sm-4 col-sm-offset-0">
                    <a href="http://wiki.teamliquid.net/starcraft2/ToD" target="_blank" class="t-portrait t-portrait-tod"></a>
                    <div class="t-name">Tod</div>
                    <div class="t-social">
                        <a class="icon-protoss" title="Protoss" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/XMGToD" target="_blank"></a>
                    </div>
                </div>

                <div id="tb" class="t-person col-md-3 col-sm-4">
                    <a href="http://wiki.teamliquid.net/starcraft2/TotalBiscuit" target="_blank" class="t-portrait t-portrait-tb"></a>
                    <div class="t-name">TotalBiscuit</div>
                    <div class="t-social">
                        <a class="icon-terran" title="Terran" href="#"></a>
                        <a class="icon-twitter" title="Twitter" href="https://twitter.com/Totalbiscuit" target="_blank"></a>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <section id="brackets">
        <div class="container">
            <h1>Brackets</h1>
        </div>
    </section>

    <section id="sponsors">
        <div class="container">
            <h1>Sponsors</h1>

            <div class="row">

                <div id="jordwoodwatches" class="t-sponsor col-md-3">
                    <a class="t-logo sponsor-jordwoodwatches" href="http://www.woodwatches.com/" target="_blank" title="www.woodwatches.com"></a>
                    <a href="http://www.woodwatches.com/" target="_blank" title="www.woodwatches.com" class="t-name">Wood Watches</a>
                    <p>It's about time someone delivered an unconventional answer to age-old wrist candy. JORD watches are designed to take people back to nature and away from today's metal &amp; rubber. We want to challenge the norm by making unique time pieces as a focal point for everyday fashion.</p>
                </div>

                <div id="breakingoutinvitational" class="t-sponsor col-md-3">
                    <a class="t-logo sponsor-breakingoutinvitational" href="http://www.teamliquid.net/forum/sc2-tournaments/449573-the-breakout-invitational-2-north-america" target="_blank" title="www.teamliquid.net"></a>
                    <a href="http://www.teamliquid.net/forum/sc2-tournaments/449573-the-breakout-invitational-2-north-america" target="_blank" title="www.teamliquid.net" class="t-name">Breaking Out</a>
                    <p>Breaking Out is a show about up &amp; coming North American Starcraft 2 players. The show aims to highlight the next "breakout" players while they're still making a name for themself through coverage of their games, some interviews, and a little bit of fun.</p>
                </div>

                <div id="videogamevotersnetwork" class="t-sponsor col-md-3">
                    <a class="t-logo sponsor-videogamevotersnetwork" href="http://vgvn.onenationofgamers.com/?ref=Destiny" target="_blank" title="videogamevoters.org"></a>
                    <a href="http://vgvn.onenationofgamers.com/?ref=Destiny" target="_blank" title="vvgvn.onenationofgamers.com" class="t-name">Video Game Voters</a>
                    <p>The Video Game Voters Network is a place for American gamers to organize and defend against threats to video games by registering to vote and letting Congress know how important this issue is to the community.</p>
                </div>

                <div id="letskungfu" class="t-sponsor col-md-3">
                    <a class="t-logo sponsor-letskungfu" href="http://www.twitch.tv/feardragon64" target="_blank" title="LetsKungFu"></a>
                    <a class="t-name" href="http://www.twitch.tv/feardragon64"  target="_blank" title="LetsKungFu">LetsKungFu</a>
                    <p>Web TV Series, focusing on eSports (StarCraft, League of Legends, etc)</p>
                </div>

            </div>

        </div>
    </section>

    <section id="t-foot">
        <footer class="container clearfix">

            <div id="t-foot-social">
                <a id="destiny-i-logo" href="/">Destiny I</a>
                <div id="t-social" class="pull-right">
                    <a href="https://www.indiegogo.com/projects/destiny-i" target="_blank" title="IndieGoGo" class="icon-f-indiegogo">IndieGoGo</a>
                    <a href="https://twitter.com/Steven_Bonnell/" target="_blank" title="Twitter" class="icon-f-twitter">Twitter</a>
                    <a href="https://www.facebook.com/Steven.Bonnell.II" target="_blank" title="Facebook" class="icon-f-facebook">Facebook</a>
                    <a href="http://www.twitch.tv/destiny" target="_blank" title="Twitch" class="icon-f-twitch">Twitch</a>
                    <a href="https://www.youtube.com/user/Destiny" target="_blank" title="Youtube" class="icon-f-youtube">Youtube</a>
                    <a href="http://www.reddit.com/r/Destiny" target="_blank" title="Reddit" class="icon-f-reddit">Reddit</a>
                </div>
            </div>

            <div id="t-foot-nav">
                <a href="/">HOME</a>  >  
                <a href="#dates">DATES</a>  >  
                <a href="#players">PLAYERS</a>  >  
                <a href="#casters">CASTERS</a>  >  
                <a href="#brackets">BRACKETS</a>  >  
                <a href="#sponsors">SPONSORS</a>
            </div>

            <div id="t-foot-links">
                <p class="pull-left">
                    <span><?=Config::$a['meta']['shortName']?> &copy; <?=date('Y')?> </span>
                    <span><a href="mailto:steven.bonnell.ii@gmail.com" title="Email Destiny">@Contact</a></span>
                </p>
                <p class="pull-right" style="text-align: right;">
                    Source code for <a href="https://github.com/destinygg/website">website</a> and <a href="https://github.com/destinygg/chat">chat</a> @ <a href="https://github.com/destinygg">Github</a>
                </p>
            </div>

        </footer>
    </section>

    <a id="back-to-top" class="icon-top" title="Scroll to top" href="#top"></a>

    <?php include Tpl::file('seg/commonbottom.php') ?>
    
</body>
</html>