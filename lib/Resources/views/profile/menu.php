<?php
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Session;
?>

<h2 class="page-title" style="margin-left: 20px;">
	<span><?= Tpl::out(Session::getCredentials ()->getUsername ()) ?></span>
	<small><i class="fa fa-envelope-o" title="<?= Tpl::out(Session::getCredentials ()->getEmail ()) ?>"></i></small>
</h2>

<section class="container">
    <ol class="breadcrumb" style="margin-bottom:0;">
      	<li><a href="/profile" title="Your account details">Account</a></li>
      	<li><a href="/profile/messages" title="Your private messages">Messages</a></li>
        <li><a href="/profile/authentication" title="Your login methods">Authentication</a></li>
    </ol>
</section>