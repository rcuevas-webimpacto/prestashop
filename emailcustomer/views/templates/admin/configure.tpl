{*
* 2007-2020 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2020 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="panel">
	<h3><i class="icon icon-tags"></i>{l s="Configuracion del cupon de descuento" mod="emailcustomer"}</h3>
	<table class="table">
		<thead>
			<tr>
				<th scope="col">{l s="ID Usuario" mod="emailcustomer"}</th>
				<th scope="col">{l s="Email" mod="emailcustomer"}</th>
				<th scope="col">{l s="Cantidad de dinero requerida" mod="emailcustomer"}</th>
				<th scope="col">{l s="Descuento en €" mod="emailcustomer"}</th>
				<th scope="col">{l s="Código descuento" mod="emailcustomer"}</th>
				<th scope="col">{l s="Acción #" mod="emailcustomer"}</th>			
			</tr>
		</thead>
		<tbody>
		{if dbcontent}
			{foreach from=$dbcontent key=key item=r}
			<tr>
				<td name="user_id"> {$r['user_id']} </td>
				<td name="email"> {$r['email']} </td>
				<td name="moneydiscount"> {$r['moneydiscount']} </td>
				<td name="discount"> {$r['discount']} </td>
				<td name="codediscount"> {$r['codediscount']} </td>
				<td name="buttons"><a href="#" class="" onclick="#" data-toggle="tooltip" data-placement="right" title="Desactivado"><i class="icon-trash"></i></a></td>
			</tr>
			{/foreach}
		{else}
			<p>No hay cupones</p>
		{/if}
		</tbody>
	</table>
</div>
