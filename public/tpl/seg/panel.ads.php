<? namespace Destiny; ?>

<section class="container">
	<div class="content clearfix" style="position: relative;">

		<div style="margin-right: 320px;" class="clearfix">
			<div class="clearfix">
				<div style="width: 40%; float: left;">
					<div class="donate-block" id="paypal-donation">
						<p>
							<a title="Visit www.paypal.com" href="https://www.paypal.com/">PayPal</a>
							contributions are welcome and appreciated, but not required.
						</p>
						<div id="paypal-form">
							<form action="https://www.paypal.com/cgi-bin/webscr" method="post" disabled="disabled">
								<input type="hidden" name="cmd" value="_donations">
								<input type="hidden" name="business" value="<?=Config::$a['paypal']['email']?>">
								<input type="hidden" name="lc" value="US">
								<input type="hidden" name="item_name" value="<?=Config::$a['paypal']['name']?>">
								<input type="hidden" name="no_note" value="0">
								<input type="hidden" name="currency_code" value="USD"> <input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHostedGuest">
								<button class="btn btn-large btn-paypal" title="PayPal - The safer, easier way to pay online!">Contribute</button>
								<div id="paypal-payment-methods" title="Payment methods"></div>
								<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
							</form>
						</div>
					</div>
				</div>
				<div style="width: 60%; float: left;">
					<div class="private-ads">
						<div class="private-ad" id="feenix-nascita">
							<a href="http://www.feenixcollection.com/" title="visit Feenix Collection"></a>
						</div>
						<div class="private-ad" id="open-broadcasters-software">
							<a href="http://obsproject.com/" title="visit Open Broadcasters Software"></a>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="public-ads">
			<div id="google-ad">
				<?if((bool) Config::$a['blocks']['pageads']):?>
				<script type="text/javascript">
				<? foreach ( Config::$a ['googleads'] ['300x250'] as $k => $v ) {
					echo (is_int ( $v )) ? "$k = $v;\r\n" : "$k = \"$v\";\r\n";
				} ?>
				</script>
				<script src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
				<?endif;?>
			</div>
		</div>

	</div>
</section>