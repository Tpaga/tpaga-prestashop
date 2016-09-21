{*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @version  Release: $Revision: 14011 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<link href="{$css|escape:'htmlall':'UTF-8'}normalize.css" rel="stylesheet" type="text/css">
<link href="{$css|escape:'htmlall':'UTF-8'}tpaga.css" rel="stylesheet" type="text/css">
<img src="{$tracking|escape:'htmlall':'UTF-8'}" alt="tracking" class="md-tracking"/>
<div class="ctwrapper">
	<div class="header_tpaga clearfix">
		<div class="centered-container">
			<div class="md-copy_tpaga">
				<span class="tpaga-logo"><img src="{$img|escape:'htmlall':'UTF-8'}logo.png" alt="logo"></span>
				{l s='La mejor experiencia de pagos online' mod='tpaga'}
			</div>
		</div>
	</div>

	<div class="container_tpaga clearfix md_wrapper_gray">
		{foreach from=$tab item=div}
			<div id="{$div.tab|escape:'htmlall':'UTF-8'}" class="{$div.style}">
				{$div.content}
			</div>
		{/foreach}
		<div class="clear"></div>
	</div>
</div>
