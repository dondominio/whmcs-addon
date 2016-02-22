<?php

$template = "
<div class=\"domain-step-options\" id=\"stepResults\">
	<div class=\"domain-checker-result-headline\">
		<p class=\"domain-checker-available\">
			" . $LANG['tpl_you_may_also_like'] . "
		</p>
	</div>
	
	<div class=\"domainresults\" id=\"primarySearchResults\">
		<div id=\"btnCheckout\" class=\"domain-checkout-area\">
			<a href=\"cart.php?a=view\" class=\"btn btn-default\">" . $LANG['tpl_go_to_checkout'] . "</a>
		</div>
		
		<div>
			" . $LANG['tpl_search_results'] . "
		</div>
		
		<table class=\"table table-curved table-hover\" id=\"searchResults\">
		<tbody>
			{{#suggestions}}
			<tr>
				<td><strong>{{domain}}</strong></td>
				
				<td class=\"text-center\">
					<span class=\"label label-success\">" . $LANG['tpl_available'] . "</span>
				</td>
				
				<td class=\"text-center\">{{price.currency_prefix}}{{price.1Y}} {{price.currency_suffix}}</td>
				
				<td class=\"text-center\">
					<div class=\"btn-group\">
						<button type=\"button\" class=\"btn btn-primary btn-sm\" onclick=\"addToCart(this, false, 'register', 1)\">
							<b class=\"glyphicon glyphicon-shopping-cart\"></b>
							
							" . $LANG['tpl_add_to_cart'] . "
						</button>
						
						<button type=\"button\" class=\"btn btn-primary btn-sm dropdown-toggle additional-options\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
							<b class=\"caret\"></b>
							
							<span class=\"sr-only\">
								" . $LANG['tpl_additional_pricing_options_for'] . " {{domain}}
							</span>
						</button>
						
						<ul class=\"dropdown-menu\" role=\"menu\">
							<li>
								<a onclick=\"addToCart(this, false, 'register', 2);return false;\">
									2 " . $LANG['tpl_years'] . " @ {{price.currency_prefix}}{{price.2Y}} {{price.currency_suffix}}
								</a>
							</li>
							
							<li>
								<a onclick=\"addToCart(this, false, 'register', 3);return false;\">
									3 " . $LANG['tpl_years'] . " @ {{price.currency_prefix}}{{price.3Y}} {{price.currency_suffix}}
								</a>
							</li>
							
							<li>
								<a onclick=\"addToCart(this, false, 'register', 4);return false;\">
									4 " . $LANG['tpl_years'] . " @ {{price.currency_prefix}}{{price.4Y}} {{price.currency_suffix}}
								</a>
							</li>
							
							<li>
								<a onclick=\"addToCart(this, false, 'register', 5);return false;\">
									5 " . $LANG['tpl_years'] . " @ {{price.currency_prefix}}{{price.5Y}} {{price.currency_suffix}}
								</a>
							</li>
							
							<li>
								<a onclick=\"addToCart(this, false, 'register', 6);return false;\">
									5 " . $LANG['tpl_years'] . " @ {{price.currency_prefix}}{{price.6Y}} {{price.currency_suffix}}
								</a>
							</li>
							
							<li>
								<a onclick=\"addToCart(this, false, 'register', 7);return false;\">
									5 " . $LANG['tpl_years'] . " @ {{price.currency_prefix}}{{price.7Y}} {{price.currency_suffix}}
								</a>
							</li>
							
							<li>
								<a onclick=\"addToCart(this, false, 'register', 8);return false;\">
									5 " . $LANG['tpl_years'] . " @ {{price.currency_prefix}}{{price.8Y}} {{price.currency_suffix}}
								</a>
							</li>
							
							<li>
								<a onclick=\"addToCart(this, false, 'register', 9);return false;\">
									5 " . $LANG['tpl_years'] . " @ {{price.currency_prefix}}{{price.9Y}} {{price.currency_suffix}}
								</a>
							</li>
							
							<li>
								<a onclick=\"addToCart(this, false, 'register', 10);return false;\">
									5 " . $LANG['tpl_years'] . " @ {{price.currency_prefix}}{{price.10Y}} {{price.currency_suffix}}
								</a>
							</li>
						</ul>
					</div>
				</td>
			</tr>
			{{/suggestions}}
		</tbody>
		</table>
	</div>
</div>
";

return $template;

?>