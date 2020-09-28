<?php
/**
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
*/

use PhpCsFixer\Config;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Product_text extends Module
{
    public function __construct()
    {
        $this->name = 'product_text';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'rcuevas-webimpacto';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Texto de productos');
        $this->description = $this->l('Modulo que permite añadir una frase única para cada producto');

        $this->confirmUninstall = $this->l('Seguro que desea desinstalar?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {

        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() && $this->_installSql() &&
            $this->registerHook('actionProductSave') &&
			$this->registerHook('displayProductButtons') &&
			$this->registerHook('displayAdminProductsMainStepLeftColumnMiddle');
    }

    public function uninstall()
    {

        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall() && $this->_uninstallSql(); //$this->alterProductTable('remove');
	}
	
	protected function _installSql() {
        $sqlInstallLang = "ALTER TABLE " . _DB_PREFIX_ . "product_lang ADD column input_product TEXT NULL";

        $returnSqlLang = Db::getInstance()->execute($sqlInstallLang);
        
        return  $returnSqlLang;
	}
	
	protected function _uninstallSql() {
		$sqlInstallLang = "ALTER TABLE " . _DB_PREFIX_ . "product_lang DROP column input_product";

		$returnSqlLang = Db::getInstance()->execute($sqlInstallLang);
		
		return $returnSqlLang;
	}

	public function hookDisplayAdminProductsMainStepLeftColumnMiddle(){
		return $this->display(__FILE__,'views/templates/hook/product-extra.tpl');
	}
	public function hookActionProductSave($params)
	{
		$campo=(string)Tools::getValue('input_product');
		$id=(int)Tools::getValue('id_product');
		if(Tools::isSubmit('input_product')){
			Db::getInstance()->update('product_lang',array('input_product'=>$campo), 'id_product = ' .$id);
		}
		
	}

	public function hookDisplayProductButtons()
	{
		$input_product= Db::getInstance()->execute("SELECT input_product from "._DB_PREFIX_."product_lang where id_product = ".(int)Tools::getValue('id_product'));
		$this->smarty->assign(array(
			$input_product=>$this->input_product));
		return $this->display(__FILE__, 'views/templates/hook/product_text.tpl');
	}
	
}