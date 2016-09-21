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
*  @author		TPAGA <info@tpaga.co>
*  @copyright TPAGA
*  @license		http://opensource.org/licenses/osl-3.0.php	Open Software License (OSL 3.0)
*/

include(dirname(__FILE__).'/../../../config/config.inc.php');
include(dirname(__FILE__).'/../../../init.php');
include(dirname(__FILE__).'/../tpaga.php');

$data = json_decode(file_get_contents('php://input'), true);

$signature = $data['signature'];
$merchantToken = $data['merchant_token'];
$purchaseOrderId = $data['purchase_order_id'];
$purchaseId = $data['purchase_id'];
$purchaseAmount = $data['purchase_amount'];
$paymentState = $data['purchase_state'];
$responseMessage = $data['payment_message'];
$paymentMethod = $data['payment_method'];
$currency = $data['purchase_currency'];
$description = $data['purchase_description'];
$installments = $data['installments'];

$tpaga = new Tpaga();

$merchantSecret = Configuration::get('TPAGA_SECRET_TOKEN');
$message = $merchantToken . $purchaseAmount . $purchaseOrderId . $purchaseId . $paymentState . $merchantSecret;
$local_signature = hash('sha256', $message);

$cart = new Cart((int)$purchaseOrderId);
if (Tools::strtoupper($signature) == Tools::strtoupper($local_signature))
{
	$state = 'TPAGA_OS_FAILED';
	if ($paymentState == 'paid')
		$state = 'PS_OS_PAYMENT';
	else
		$state = 'TPAGA_OS_PENDING';

	if (!Validate::isLoadedObject($cart))
		$errors[] = 'Invalid Cart ID';
	else
	{
		$currency_cart = new Currency((int)$cart->id_currency);
		if ($cart->orderExists())
		{
			$order = new Order((int)Order::getOrderByCartId($cart->id));
				
			if (_PS_VERSION_ < '1.5')
			{
				$current_state = $order->getCurrentState();
				if ($current_state != Configuration::get('PS_OS_PAYMENT'))
				{
					$history = new OrderHistory();
					$history->id_order = (int)$order->id;
					$history->changeIdOrderState((int)Configuration::get($state), $order->id);
					$history->addWithemail(true);
				}
			}
			else
			{
				$current_state = $order->current_state;
				if ($current_state != Configuration::get('PS_OS_PAYMENT'))
				{
					$history = new OrderHistory();
					$history->id_order = (int)$order->id;
					$history->changeIdOrderState((int)Configuration::get($state), $order, true);
					$history->addWithemail(true);
				}
			}
		}
		else
		{
			$customer = new Customer((int)$cart->id_customer);
			Context::getContext()->customer = $customer;
			Context::getContext()->currency = $currency_cart;

			$tpaga->validateOrder((int)$cart->id, (int)Configuration::get($state), (float)$cart->getordertotal(true), 'Tpaga', null, array(), (int)$currency_cart->id, false, $customer->secure_key);
			Configuration::updateValue('TPAGA_CONFIGURATION_OK', true);
			$order = new Order((int)Order::getOrderByCartId($cart->id));
		}

		if ($state != 'PS_OS_PAYMENT')
		{
			foreach ($order->getProductsDetail() as $product)
				StockAvailable::updateQuantity($product['product_id'], $product['product_attribute_id'], + (int)$product['product_quantity'], $order->id_shop);
		}
	
	}
}
?>
