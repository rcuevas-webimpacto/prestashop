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

if (!defined('_PS_VERSION_')) {
    exit;
}

class Product_text extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'product_text';
        $this->tab = 'others'; //'front_office_features';
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
            $this->registerHook('adminProductsExtra') &&
            $this->registerHook('actionProductSave') &&
            $this->registerHook('actionProductUpdate') &&
            $this->registerHook('adminProductsMainStepLeftColumnMiddle') &&
            $this->registerHook('productAdditionalInfo');
    }

    public function uninstall()
    {
        //Configuration::deleteByName('PRODUCT_TEXT_LIVE_MODE');

        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall() && $this->_uninstallSQL();;
    }

    /**
     * Modifications sql du module
     * @return boolean
     */
    protected function _installSql() {
        $sqlInstallLang = "ALTER TABLE " . _DB_PREFIX_ . "product_lang ADD column custom_field_lang_wysiwyg TEXT NULL";

        $returnSqlLang = Db::getInstance()->execute($sqlInstallLang);
        
        return  $returnSqlLang;
    }

    /**
     * Suppression des modification sql du module
     * @return boolean
     */
    protected function _unInstallSql() {
         $sqlInstallLang = "ALTER TABLE " . _DB_PREFIX_ . "product_lang DROP column product_text";
 
         $returnSqlLang = Db::getInstance()->execute($sqlInstallLang);
         
         return $returnSqlLang;
     }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitProduct_textModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitProduct_textModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Nuevos Ajustes'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type'=>'textarea',
                        'label'=>$this->l('Nuevo area de texto'),
                        'name' =>'text_field1',
                        'desc'=>$this->l('Descripicion del area de texto'),
                        'autoload_rte'=> true,
                        'lang'=> true
                    )
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Admin.Actions'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'text_field1' => Configuration::get('text_field1', true),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    public function hookDisplayAdminProductsMainStepLeftColumnMiddle($params){
        $product=new Product($params['id_product']);
        $languages=Language::getLanguages($active);
        $this->context->smarty->assign(array(
            'languages'=>$languages,
            'custom_field_lang_wysiwyg'=>$product->custom_field_lang_wysiwyg,
            'default_language'=>$this->context->employee->id_lang,
        ));

        return $this->display(__FILE__,'views/templates/hook/product-extra.tpl');
    }

    public function hookActionProductUpdate($params)
    {
        $product = new Product($params['id_product']);
        $id_product = (int)Tools::getValue('id_product');
        Db::getInstance()->update('product_lang', pSQL(Tools::getValue('product_text'),'id_product = ' .$id_product));
        $this->context->controller->_errors[] = Tools::displayError('Error');
        return $this->hookDisplayProductAdditionalInfo(__FUNCTION__);
    
    }

    public function hookDisplayAdminProductsExtra()
    {
        /* Place your code here. */
    }

    public function hookDisplayProductAdditionalInfo()
    {
        /* Place your code here. */
        $product=new Product();
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
        $this->smarty->assign(array(
            'text_field1'=>$product->field->text_field1,
        ));
        return $this->display(__FILE__,'product-extra.tpl');
    }
}
//actionProductSave
//actionProductUpdate
//displayAdminProductsMainStepRightColumnBottom
//displayProductAdditionalInfo
