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

<script src={$urljs} type="text/javascript"></script>
<link rel="stylesheet" href={$urlcss}>
<section id="content-hookemailcustomer" class="card mb-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
            {if coupon}
                {foreach from=$coupon key=key item=i}
                <div class="base">20% de descuento<br/>Código descuento: {$i['cuponcode']}</div>
                <canvas id="scratch" width="300" height="60"></canvas>
                {/foreach}
            {else}
                <p>No hay cupones</p>
            {/if}
            </div>
        </div>
    </div>
</section>