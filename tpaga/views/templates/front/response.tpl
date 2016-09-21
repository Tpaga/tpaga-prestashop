{*
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
*  @author		TPAGA <info@tpga.co>
*  @copyright 2016 TPaga
*  @license		http://opensource.org/licenses/osl-3.0.php	Open Software License (OSL 3.0)
*}
<link rel="stylesheet" href="{$css_dir}global.css" type="text/css" media="all">
<link href="{$css|escape:'htmlall':'UTF-8'}tpaga.css" rel="stylesheet" type="text/css">
{if $valid && $successful}
	<center>
		<table class="table-response">
			<tr align="center">
				<th colspan="2"><h1 class="md-h1">{l s='Purchase Data' mod='tpaga'}</h1></th>
			</tr>
			<tr align="left">
				<td>{l s='Transaction State' mod='tpaga'}</td>
				<td>{$estadoTx|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr align="left">
				<td>{l s='Transaction ID' mod='tpaga'}</td>
				<td>{$transactionId|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr align="left">
				<td>{l s='Total Value' mod='tpaga'}</td>
				<td>${$value|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr align="left">
				<td>{l s='installments' mod='tpaga'}</td>
				<td>{$installments|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr align="left">
				<td>{l s='Currency' mod='tpaga'}</td>
				<td>{$currency|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr align="left">
				<td>{l s='Description' mod='tpaga'}</td>
				<td>{$description|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr align="left">
				<td>{l s='Entity' mod='tpaga'}</td>
				<td>{$lapPaymentMethod|escape:'htmlall':'UTF-8'}</td>
			</tr>
		</table>
		<p/>
		<h1>{$messageApproved|escape:'htmlall':'UTF-8'}</h1>
	</center>
{elseif !$valid && !$successful}
	<h1><center>{l s='Your payment could not be completed, please contact your bank.' mod='tpaga'}</center></h1>
{else}
	<h1><center>{l s='The request is incorrect! There is an error in the digital signature.' mod='tpaga'}</center></h1>
{/if}
