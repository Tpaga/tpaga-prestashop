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
*  @author    TPAGA <info@tpga.co>
*  @copyright 2016 TPaga
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

if (!defined('_PS_VERSION_'))
	exit;

class Tpaga extends PaymentModule {

private $_postErrors = array();

public function __construct()
{
  $this->name = 'tpaga';
	$this->tab = 'payments_gateways';
	$this->version = '1.0.0';
  $this->author = 'Tpaga';
	$this->need_instance = 0;
	$this->currencies = true;
	$this->currencies_mode = 'checkbox';
	parent::__construct();

  $this->displayName = $this->l('Tpaga');
  $this->description = $this->l('Tpaga webcheckout integration for Prestashop');

	$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
	/* Backward compatibility */
	if (_PS_VERSION_ < '1.5')
		require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');

	$this->_checkForUpdates();
}

public function install()
{
	$this->_createStates();

	if (!parent::install()
		|| !$this->registerHook('payment')
		|| !$this->registerHook('paymentReturn'))
		return false;
	return true;
}

public function uninstall()
{
	if (!parent::uninstall()
		|| !Configuration::deleteByName('TPAGA_MERCHANT_TOKEN')
		|| !Configuration::deleteByName('TPAGA_SECRET_TOKEN')
    || !Configuration::deleteByName('TPAGA_TEST')
		|| !Configuration::deleteByName('TPAGA_OS_PENDING')
		|| !Configuration::deleteByName('TPAGA_OS_FAILED')
    || !Configuration::deleteByName('TPAGA_OS_REJECTED')
  )
		return false;
	return true;
}

public function getContent()
{
	$html = '';

	if (isset($_POST) && isset($_POST['submitTpaga']))
	{
		$this->_postValidation();
		if (!count($this->_postErrors))
    {
			$this->_saveConfiguration();
			$html .= $this->displayConfirmation($this->l('Settings updated'));
		}
    else
			foreach ($this->_postErrors as $err)
				$html .= $this->displayError($err);
	}
	return $html.$this->_displayAdminTpl();
}

private function _displayAdminTpl()
{
	$this->context->smarty->assign(array(
		'tab' => array(
			'intro' => array(
				'title' => $this->l('How to configure'),
				'content' => $this->_displayHelpTpl(),
        'icon' => '../modules/tpaga/img/info-icon.gif',
				'tab' => 'conf',
				'selected' => (Tools::isSubmit('submitTpaga') ? false : true),
				'style' => 'config_tpaga'
			),
			'credential' => array(
				'title' => $this->l('Credentials'),
				'content' => $this->_displayCredentialTpl(),
        'icon' => '../modules/tpaga/img/credential.png',
				'tab' => 'crendeciales',
				'selected' => (Tools::isSubmit('submitTpaga') ? true : false),
				'style' => 'credentials_tpaga'
			),
		),
		'img' => '../modules/tpaga/img/',
    'css' => '../modules/tpaga/css/',
		'lang' => ($this->context->language->iso_code != 'en' || $this->context->language->iso_code != 'es' ? 'en' : $this->context->language->iso_code)
	));

	return $this->display(__FILE__, 'views/templates/admin/admin.tpl');
}

private function _displayHelpTpl()
{
	return $this->display(__FILE__, 'views/templates/admin/help.tpl');
}

private function _displayCredentialTpl()
{
	$this->context->smarty->assign(array(
		'formCredential' => './index.php?tab=AdminModules&configure=tpaga&token='.Tools::getAdminTokenLite('AdminModules').
		'&tab_module='.$this->tab.'&module_name=tpaga',
		'credentialTitle' => $this->l('Log in'),
		'credentialInputVar' => array(
			'merchant_token' => array(
				'name' => 'merchant_token',
				'required' => true,
				'value' => (Tools::getValue('merchant_token') ? Tools::safeOutput(Tools::getValue('merchant_token')) :
        Tools::safeOutput(Configuration::get('TPAGA_MERCHANT_TOKEN'))),
				'type' => 'text',
				'label' => $this->l('Merchant'),
        'desc' => $this->l('Your merchant identifier which could be found in the Tpaga administrative panel'),
			),
			'secret_token' => array(
				'name' => 'secret_token',
				'required' => true,
				'value' => (Tools::getValue('secret_token') ? Tools::safeOutput(Tools::getValue('secret_token')) :
				Tools::safeOutput(Configuration::get('TPAGA_SECRET_TOKEN'))),
				'type' => 'text',
        'label' => $this->l('Secret Token'),
				'desc' => $this->l('Secret token provided by Tpaga'),
			),
			'test' => array(
				'name' => 'test',
        'required' => true,
        'value' => (Tools::getValue('test') ? Tools::safeOutput(Tools::getValue('test')) : Tools::safeOutput(Configuration::get('TPAGA_TEST'))),
				'type' => 'radio',
				'values' => array('true', 'false'),
				'label' => $this->l('Mode Test'),
        'desc' => $this->l('Defines the environment where the transaction will be performed.'),
			))));
	return $this->display(__FILE__, 'views/templates/admin/credential.tpl');
}


public function hookPayment($params)
{
	if (!$this->active)
		return;
		
	$this->context->smarty->assign(array(
    'css' => '../modules/tpaga/css/',
		'module_dir' => _PS_MODULE_DIR_.$this->name.'/'
	));

  return $this->display(__FILE__, 'views/templates/hook/tpaga_payment.tpl');
}

private function _postValidation()
{
	if (!Validate::isCleanHtml(Tools::getValue('merchant_token'))
		|| !Validate::isGenericName(Tools::getValue('merchant_token')))
    $this->_postErrors[] = $this->l('You must indicate the merchant token');

	if (!Validate::isCleanHtml(Tools::getValue('secret_token'))
		|| !Validate::isGenericName(Tools::getValue('secret_token')))
    $this->_postErrors[] = $this->l('You must provide the Tpaga WebCheckout token');

	if (!Validate::isCleanHtml(Tools::getValue('test'))
		|| !Validate::isGenericName(Tools::getValue('test')))
		$this->_postErrors[] = $this->l('You must indicate if the transaction mode is test or not');

}

private function _saveConfiguration()
{
  Configuration::updateValue('TPAGA_MERCHANT_TOKEN', (string)Tools::getValue('merchant_token'));
  Configuration::updateValue('TPAGA_SECRET_TOKEN', (string)Tools::getValue('secret_token'));
	Configuration::updateValue('TPAGA_TEST', Tools::getValue('test'));
}

private function _createStates()
{
  if (!Configuration::get('TPAGA_OS_PENDING'))
	{
		$order_state = new OrderState();
		$order_state->name = array();
		foreach (Language::getLanguages() as $language)
			$order_state->name[$language['id_lang']] = 'Pending';

		$order_state->send_email = false;
		$order_state->color = '#FEFF64';
		$order_state->hidden = false;
		$order_state->delivery = false;
		$order_state->logable = false;
		$order_state->invoice = false;

		if ($order_state->add())
		{
			$source = dirname(__FILE__).'/img/logo.jpg';
			$destination = dirname(__FILE__).'/../../img/os/'.(int)$order_state->id.'.gif';
			copy($source, $destination);
		}
		Configuration::updateValue('TPAGA_OS_PENDING', (int)$order_state->id);
	}

	if (!Configuration::get('TPAGA_OS_FAILED'))
	{
		$order_state = new OrderState();
		$order_state->name = array();
		foreach (Language::getLanguages() as $language)
			$order_state->name[$language['id_lang']] = 'Failed Payment';

		$order_state->send_email = false;
		$order_state->color = '#8F0621';
		$order_state->hidden = false;
		$order_state->delivery = false;
		$order_state->logable = false;
		$order_state->invoice = false;

		if ($order_state->add())
		{
			$source = dirname(__FILE__).'/img/logo.jpg';
			$destination = dirname(__FILE__).'/../../img/os/'.(int)$order_state->id.'.gif';
			copy($source, $destination);
		}
		Configuration::updateValue('TPAGA_OS_FAILED', (int)$order_state->id);
	}

	if (!Configuration::get('TPAGA_OS_REJECTED'))
	{
		$order_state = new OrderState();
		$order_state->name = array();
		foreach (Language::getLanguages() as $language)
			$order_state->name[$language['id_lang']] = 'Rejected Payment';

		$order_state->send_email = false;
		$order_state->color = '#8F0621';
		$order_state->hidden = false;
		$order_state->delivery = false;
		$order_state->logable = false;
		$order_state->invoice = false;

		if ($order_state->add())
		{
			$source = dirname(__FILE__).'/img/logo.jpg';
			$destination = dirname(__FILE__).'/../../img/os/'.(int)$order_state->id.'.gif';
			copy($source, $destination);
		}
		Configuration::updateValue('TPAGA_OS_REJECTED', (int)$order_state->id);
	}
}

private function _checkForUpdates()
{
	// Used by PrestaShop 1.3 & 1.4
	if (version_compare(_PS_VERSION_, '1.5', '<') && self::isInstalled($this->name))
		foreach (array('2.0') as $version)
		{
			$file = dirname(__FILE__).'/upgrade/upgrade-'.$version.'.php';
      if (Configuration::get('TPAGA') < $version && file_exists($file))
			{
				include_once($file);
            call_user_func('upgrade_module_'.str_replace('.', '_', $version), $this);
			}
		}
}
}
?>
