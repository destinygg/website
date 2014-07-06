<?
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=$model->title?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/commontop.php') ?>
<?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body id="tournament-i">

    <div id="tournament-i-page" style="display: none;">
        <a title="Support us! Indigogo" href="https://www.indiegogo.com/projects/destiny-i" id="tournament-i-logo"></a>
    </div>

    <?php include Tpl::file('seg/commonbottom.php') ?>

    <script>
    $(window).on('load', function(){
        $('#tournament-i-page').show();
        $('#tournament-i-logo').addClass('in');
    });
    </script>
  
</body>
</html>