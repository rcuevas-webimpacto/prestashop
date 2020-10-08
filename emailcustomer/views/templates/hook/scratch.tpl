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
<section id="content-hookemailcustomer" class="mb-3">{*class="card mb-3"*}
    <div class="card-body body-scratch row m-0">
        <div class="col-lg-6 col-md-6 offset-lg-3 offset-md-3 pb-4 border">
            <div class="row">
                <div class="col-md-12 col-lg-12 text-center">
                    <p class="scratchname"><br/>¡Rasca y Gana!</p>
                </div>
                <div class="col-lg-8 col-md-8 offset-lg-2 offset-md-2 text-center">
                    <div class="tarjeta">
                        {if coupon}
                            {foreach from=$coupon key=key item=i}
                            <div class="base"><strong>20% </strong>descuento. Código: <strong>{$i['cuponcode']}</strong></div>
                            <canvas id="scratch" width="300" height="60"></canvas>
                            {/foreach}
                        {else}
                            <p>No hay cupones</p>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>