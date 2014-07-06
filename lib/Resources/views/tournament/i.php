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
<body id="trnmt-i">

    <div id="trnmt-i-page" style="display: none;">
        <div id="trnmt-i-info">
            <a title="Support us! Indigogo" href="https://www.indiegogo.com/projects/destiny-i" id="trnmt-i-logo"></a>
            <a title="Support us! Indigogo"  href="https://www.indiegogo.com/projects/destiny-i" id="trnmt-i-coming"></a>
        </div>
    </div>

    <?php include Tpl::file('seg/commonbottom.php') ?>

    <script>
    $(window).on('load', function(){
        $('#trnmt-i-page').show();
    });
    </script>
  
</body>
</html>