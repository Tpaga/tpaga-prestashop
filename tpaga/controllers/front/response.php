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

class TpagaResponseModuleFrontController extends ModuleFrontController
{
	public function initContent()
	{
		parent::initContent();
		if ($_REQUEST['signature'] === 'customererror')
		{
			$message = explode("=" , $_REQUEST['controller'])[1];

			$this->context->smarty->assign(
				array(
					'valid' => false,
					'successful' => false,
					'css' => '../modules/tpaga/css/'
				)
			);

			$this->setTemplate('response.tpl');
		}
		else
		{
			$this->showResponse();
		}

	}

	private function showResponse()
	{
		$description = explode("=", $_REQUEST['controller'])[1];

		$receivedSignature = $_REQUEST['signature'];
		$merchantToken = $_REQUEST['merchant_token'];
		$purchaseOrderId =	$_REQUEST['purchase_order_id'];
		$purchaseId =  $_REQUEST['purchase_id'];
		$purchaseAmount =  $_REQUEST['purchase_amount'];
		$paymentState =  $_REQUEST['purchase_state'];
		$responseMessage =	$_REQUEST['payment_message'];
		$paymentMethod =	$_REQUEST['payment_method'];
		$currency =  $_REQUEST['purchase_currency'];
		$installments = $_REQUEST['installments'];
		$description = $description;

		$this->context = Context::getContext();
		$tpaga = new Tpaga();
		$cart = new Cart((int)$purchaseOrderId);

		$merchantSecret = Configuration::get('TPAGA_SECRET_TOKEN');
		$message = $merchantToken . $purchaseAmount . $purchaseOrderId . $purchaseId . $paymentState . $merchantSecret;
		$signatureLocal = hash('sha256', $message);

		$messageApproved = '';
		if ($paymentState == 'paid')
		{
			$txState = $tpaga->l('Transaction Approved');
			$messageApproved = $tpaga->l('Thank you for your purchase!');
		}
		else
		{
			$txState = $responseMessage;
		}

		if (Tools::strtoupper($receivedSignature) == Tools::strtoupper($signatureLocal))
		{
			if (!($cart->orderExists()))
			{
				$customer = new Customer((int)$cart->id_customer);
				$this->context->customer = $customer; $tpaga->validateOrder((int)$cart->id, Configuration::get('TPAGA_OS_PENDING'), (float)$cart->getordertotal(true), 'Tpaga', null, array(), (int)$cart->id_currency, false, $customer->secure_key);
				Configuration::updateValue('TPAGA_CONFIGURATION_OK', true);
			}
			
			$this->context->smarty->assign(
				array(
					'estadoTx' => $txState,
					'transactionId' => $purchaseId,
					'referenceCode' => $purchaseOrderId,
					'value' => $purchaseAmount,
					'currency' => $currency,
					'description' => $description,
					'lapPaymentMethod' => $paymentMethod,
					'messageApproved' => $messageApproved,
					'installments' => $installments,
					'valid' => true,
					'successful' => true,
					'css' => '../modules/tpaga/css/'
				)
			);

		}
		else
		{
			$this->context->smarty->assign(
				array(
					'valid' => false,
					'successful' => false,
					'css' => '../modules/tpaga/css/'
				)
			);
		}

		$this->setTemplate('response.tpl');
	}
}
?>
