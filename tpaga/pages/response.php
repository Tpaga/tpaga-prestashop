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

include(dirname(__FILE__).'/../../../config/config.inc.php');
include(dirname(__FILE__).'/../../../init.php');
include(dirname(__FILE__).'/../tpaga.php');
include(dirname(__FILE__).'/../../../header.php');

$tpaga = new Tpaga();

$purchase_description = "";
$merchant_token = "";
$purchase_order_id = "";
$purchase_amount = "";
$purchase_tax = "";
$purchase_id = "";
$customer_email = "";
$signature = "";
$purchase_state = "";
$purchase_date = "";
$purchase_currency = "";
$payment_method = "";
$installments = "";
$payment_message = "";


if ($_REQUEST['signature'] != 'customererror' && isset($_REQUEST['signature'])) {

    $signature = $_REQUEST['signature'];

    if (isset($_REQUEST['purchase_description']))
        $purchase_description = $_REQUEST['purchase_description'];

    if (isset($_REQUEST['merchant_token']))
        $merchant_token = $_REQUEST['merchant_token'];

    if (isset($_REQUEST['purchase_order_id']))
        $purchase_order_id = $_REQUEST['purchase_order_id'];

    if (isset($_REQUEST['purchase_amount']))
        $purchase_amount = $_REQUEST['purchase_amount'];

    if (isset($_REQUEST['purchase_tax']))
        $purchase_tax = $_REQUEST['purchase_tax'];

    if (isset($_REQUEST['purchase_id']))
        $purchase_id = $_REQUEST['purchase_id'];

    if (isset($_REQUEST['customer_email']))
        $customer_email = $_REQUEST['customer_email'];

    if (isset($_REQUEST['purchase_state'])) {
        $purchase_state = $_REQUEST['purchase_state'];
        if ($purchase_state == 'paid') {
            $successful = true;
        } else {
            $successful = false;
        }
    }

    if (isset($_REQUEST['purchase_date']))
        $purchase_date = $_REQUEST['purchase_date'];

    if (isset($_REQUEST['purchase_currency']))
        $purchase_currency = $_REQUEST['purchase_currency'];

    if (isset($_REQUEST['payment_method']))
        $payment_method = $_REQUEST['payment_method'];

    if (isset($_REQUEST['installments']))
        $installments = $_REQUEST['installments'];

    if (isset($_REQUEST['payment_message']))
        $payment_message = $_REQUEST['payment_message'];
    else if ($purchase_state)
        $payment_message = $tpaga->l('We got some communication troubles, please chek if debit was made');

    $purchase_tax = number_format($purchase_tax, 1, '.', '');

    $merchant_secret = Configuration::get('TPAGA_SECRET_TOKEN');
    $message = $merchant_token . $purchase_amount . $purchase_order_id . $purchase_id . $purchase_state . $merchant_secret;
    $local_signature = hash('sha256', $message);

    $cart = new Cart((int)$purchase_order_id);

    if (Tools::strtoupper($signature) == Tools::strtoupper($local_signature)) {

        if (!($cart->orderExists())) {
            $customer = new Customer((int)$cart->id_customer);
            Context::getContext()->customer = $customer;
            $tpaga->validateOrder((int)$cart->id, Configuration::get('TPAGA_OS_PENDING'), (float)$cart->getordertotal(true), 'Tpaga', null, array(), (int)$cart->id_currency, false, $customer->secure_key);
        }

        Context::getContext()->smarty->assign(
            array(
                'purchase_state' => $purchase_state,
                'purchase_order_id' => $purchase_order_id,
                'customer_email' => $customer_email,
                'purchase_amount' => $purchase_amount,
                'purchase_tax' => $purchase_tax,
                'installments' => $installments,
                'purchase_currency' => $purchase_currency,
                'purchase_description' => $purchase_description,
                'payment_message' => $payment_message,
                'valid' => true,
                'successful' => $successful,
                'css' => '../modules/tpaga/css/'
            )
        );
    } else {
        Context::getContext()->smarty->assign(
            array(
                'valid' => false,
                'css' => '../modules/tpaga/css/'
            )
        );
    }
    Context::getContext()->smarty->display(dirname(__FILE__).'/../views/templates/front/response.tpl');
    include(dirname(__FILE__).'/../../../footer.php');
}
else {
    Context::getContext()->smarty->assign(
        array(
            'payment_message' => $payment_message,
            'valid' => false,
            'css' => '../modules/tpaga/css/'
        )
    );
}

?>
