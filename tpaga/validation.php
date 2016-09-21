<?php
/**
* 2016 TPAGA
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    TPAGA <info@tpaga.co>
*  @copyright TPAGA
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/tpaga.php');

$tpaga = new Tpaga();

$cart = Context::getContext()->cart;
$customer = Context::getContext()->customer;
$billing_address = new Address(Context::getContext()->cart->id_address_invoice);
$billing_address->country = new Country($billing_address->id_country);
$delivery_address = new Address(Context::getContext()->cart->id_address_delivery);
$delivery_address->country = new Country($delivery_address->id_country);
$products = $cart->getProducts();
$cart_details = $cart->getSummaryDetails(null, true);

$description = '';
foreach ($products as $product)
	$description .= $product['name'].',';

$currency = new Currency((int)$cart->id_currency);

$test = 0;
$gateway_url = 'https://webcheckout.tpaga.co/checkout';
if (Configuration::get('TPAGA_TEST') == 'true')
{
	$test = 1;
	$gateway_url = 'https://staging.webcheckout.tpaga.co/checkout';
}

if (!Validate::isLoadedObject($customer) || !Validate::isLoadedObject($billing_address) && !Validate::isLoadedObject($currency))
{
	Logger::addLog('Issue loading customer, address and/or currency data');
	die('An unrecoverable error occured while retrieving you data');
}

$signature = hash('sha256', Configuration::get('TPAGA_MERCHANT_TOKEN').$cart->getordertotal(true).(int)$cart->id.Configuration::get('TPAGA_SECRET_TOKEN'));

if ($cart_details['total_tax'] != 0)
	$base = $cart_details['total_price_without_tax'] - $cart_details['total_shipping_tax_exc'];
else
	$base = 0;

if (Configuration::get('PS_SSL_ENABLED') || (!empty($_SERVER['HTTPS']) && Tools::strtolower($_SERVER['HTTPS']) != 'off'))
{
	if (method_exists('Tools', 'getShopDomainSsl'))
		$url = 'https://'.Tools::getShopDomainSsl().__PS_BASE_URI__.'/modules/'.$tpaga->name.'/';
	else
		$url = 'https://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/'.$tpaga->name.'/';
}
else
	$url = 'http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'/modules/'.$tpaga->name.'/';

?>

<center>
	<img src="<?php echo $url; ?>img/logo.png" height="50" width="135"/>
	</br>
	<?php echo $tpaga->l('Estas siendo direccionado a Tpaga WebCheckout'); ?>
</center>

<form class="md-form" id="payment_form" name="payment_form" method="post" action="<?php echo Tools::safeOutput($gateway_url); ?>">
	<input type="hidden" name="purchase[merchant_token]" id="merchantId" value="<?php echo Tools::safeOutput(Configuration::get('TPAGA_MERCHANT_TOKEN')); ?>" />
	<input type="hidden" name="purchase[purchase_order_id]" id="referenceCode" value="<?php echo Tools::safeOutput((int)$cart->id); ?>" />
	<input type="hidden" name="purchase[purchase_description]" id="description" value="<?php echo Tools::safeOutput(trim($description, ',')); ?>" />
	<input type="hidden" name="purchase[purchase_amount]" id="amount" value="<?php echo Tools::safeOutput($cart->getordertotal(true)); ?>" />
  <input type="hidden" name="purchase[purchase_tax]" id="tax" value="<?php echo Tools::safeOutput($cart_details['total_tax']); ?>" />
	<input type="hidden" name="purchase[purchase_signature]" id="signature" value="<?php echo Tools::safeOutput($signature); ?>" />
	<input type="hidden" name="purchase[purchase_currency]" id="currency" value="<?php echo Tools::safeOutput($currency->iso_code); ?>" />
  <input type="hidden" name="purchase[customer_email]" id="buyerEmail" value="<?php echo Tools::safeOutput($customer->email); ?>" />
  <input type="hidden" name="purchase[customer_firstname]" id="payerFullName" value="<?php echo Tools::safeOutput($customer->firstname); ?>" />
  <input type="hidden" name="purchase[customer_lastname]" id="payerFullName" value="<?php echo Tools::safeOutput($customer->lastname); ?>" />
  <input type="hidden" name="purchase[address_street]" id="shippingAddress" value="<?php echo Tools::safeOutput($delivery_address->address1); ?>" />
	<input type="hidden" name="purchase[customer_phone]" id="telephone" value="<?php echo Tools::safeOutput($billing_address->phone); ?>" />
	<input type="hidden" name="purchase[address_city]" id="billingCity" value="<?php echo Tools::safeOutput($billing_address->city); ?>" />
</form>

<script type="text/javascript">
	window.onload = function() {
		document.payment_form.submit();
	};
</script>
