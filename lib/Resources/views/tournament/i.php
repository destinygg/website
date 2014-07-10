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
            <a title="Support us! Indigogo" href="javascript;" id="trnmt-i-logo"></a>
            <a title="Support us! Indigogo"  href="javascript;" id="trnmt-i-coming"></a>
        </div>
    </div>

    <?php include Tpl::file('seg/commonbottom.php') ?>

    <script>
    $(function(){
        $(window).on('load', function(){
            $('#trnmt-i-page').show();
        });
        $('#trnmt-i-info a').on('click', function(){
            _gaq.push(['_trackEvent', 'outbound', 'indiegogo', 'https://www.indiegogo.com/projects/destiny-i']);
            window.location.href = 'https://www.indiegogo.com/projects/destiny-i';
            return false;
        });
    });
    </script>
  
</body>
</html>