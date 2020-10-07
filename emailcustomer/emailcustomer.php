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

class Emailcustomer extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'emailcustomer';
        $this->tab = 'emailing';
        $this->version = '1.0.0';
        $this->author = 'rcuevas-webimpacto';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Email for the customer');
        $this->description = $this->l('Modulo que envia un email al cliente para informar del dinero gastado cuando hace una compra ');

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
            $this->registerHook('actionValidateOrder') &&
            $this->registerHook('actionFrontControllerSetMedia') &&
            $this->registerHook('displayOrderConfirmation') &&
            $this->registerHook('displayOrderConfirmation1');
    }

    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall() && $this->_uninstallSql();
    }

    protected function _installSql(){
        $sqlcuponglobal=Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'cuponglobal(id int(10) NOT NULL AUTO_INCREMENT, primary key(id), cantidadgastada float(20,6), cuponcode varchar(255), id_customer int(10), email_customer varchar(255), firstname_customer varchar(255), lastname_customer varchar(255)) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;');
        return $sqlcuponglobal;
    }

    protected function _uninstallSql()
    {
        $sqluninstall1=Db::getInstance()->execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'emailcustomer');
        $sqluninstall2=Db::getInstance()->execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'cuponglobal');
        return $sqluninstall1.$sqluninstall2;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('guardar')) == true) { //submitEmailcustomerModule
            $this->postProcess();
        } else if(((bool)Tools::isSubmit('guardar2')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        //select query works fine but I added query with left outer join
        //$dbcontent=Db::getInstance()->executeS("SELECT `moneydiscount`,`discount`,`codediscount`,`user_id`,`date` from "._DB_PREFIX_."emailcustomer order by `user_id` asc");
        //left outer join query
        $dbcontent=Db::getInstance()->executeS("SELECT ps_emailcustomer.moneydiscount,ps_emailcustomer.discount,ps_emailcustomer.codediscount,ps_emailcustomer.user_id,ps_emailcustomer.date,ps_customer.email from ps_emailcustomer left outer join ps_customer on ps_emailcustomer.user_id=ps_customer.id_customer order by ps_emailcustomer.user_id asc");
        $this->context->smarty->assign('dbcontent', $dbcontent);
        //

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
        $helper->submit_action = 'guardar'; //submitEmailcustomerModule
        $helper->submit_action = 'guardar2';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues('user_id', 'moneydiscount', 'discount', 'codediscount'), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm(), $this->configForm2()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Configuracion del cupon descuento INDIVIDUAL'),
                    'icon' => 'icon-cogs',
                    'id'=> 'formemail',
                    'method'=> $_POST,
                ),

                'input' => array(
                    array(
                        'type' => 'html',
                        'label' => $this->l('ID del usuario'),
                        'desc' => $this->l('ID del usuario que recibirá el cupón'),
                        'name' => 'user_id',
                        'html_content'=> '<input type="number" name="user_id">'
                    ),
                    array(
                        'type' => 'html',
                        'label' => $this->l('Importe en € a gastar'),
                        'name' => 'moneydiscount',
                        'desc' => $this->l('El importe que el cliente debe gastar para que se genere el cupón descuento'),
                        'html_content' => '<input type="number" name="moneydiscount">',
                        'suffix'=>'€',
                        'required'=> true,
                    ),
                    array(
                        'type' => 'html',
                        'label' => $this->l('€ de Descuento'),
                        'desc' => $this->l('Descuento en € que se le hará al usuario cuando llegue a la cantidad establecida'),
                        'name' => 'discount',
                        'html_content'=> '<input type="number" name="discount">',
                        'suffix'=>'€',
                        'required'=> true,
                        
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Código del cupon'),
                        'desc' => $this->l('Código que el usuario tendrá que introducir para que se realice el descuento'),
                        'name' => 'codediscount',
                        'required'=> true,
                    ),

                ),
                'submit' => array(
                    'title' => $this->l('Guardar'),
                    'name'=> 'guardar',
                ),
            ),
        );
    }

    protected function configForm2()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Configuracion del cupon descuento GLOBAL'),
                    'icon' => 'icon-cogs',
                    'id'=> 'formemailglobal',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Cantidad a gastar'),
                        'desc' => $this->l('Cantidad que tiene que gastar el cliente para que se le genere el cupón global'),
                        'name' => 'MINIMOREQUERIDO',
                        'required'=> true,
                        'suffix' => '€',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Guardar'),
                    'name'=> 'guardar2',
                ),
            )
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'moneydiscount' => Configuration::get('moneydiscount'),
            'discount' => Configuration::get('discount'),
            'codediscount'=>Configuration::get('codediscount'),
            'user_id'=> Configuration::get('user_id'),
            'MINIMOREQUERIDO' => Configuration::get('MINIMOREQUERIDO'),
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

        if (Tools::isSubmit('guardar')) {
            $id_emailcustomer=(int)Tools::getValue('id_emailcustomer');
            $moneydiscount=(int)(Tools::getValue('moneydiscount'));
            $discount=(int)(Tools::getValue('discount'));
            $codediscount=(string)(Tools::getValue('codediscount'));
            $user_id=(int)(Tools::getValue('user_id'));

            Db::getInstance()->insert('emailcustomer', array(
                'moneydiscount'=>$moneydiscount,
                'discount'=>$discount,
                'codediscount'=>$codediscount,
                'user_id'=>$user_id,
            ));
        } else if (Tools::isSubmit('guardar2')) {
            Configuration::updateValue('MINIMOREQUERIDO',Tools::getValue('MINIMOREQUERIDO'));
        }
    }

    public function hookActionValidateOrder($params)
    {
        $numrows=Db::getInstance()->getValue(sprintf("SELECT COUNT(*) AS num FROM ps_emailcustomer WHERE user_id= %d"),(int)pSQL($params['customer']->id));

        if ($numrows == 0) {
            //cupones para cualquier usuario
            $customer_id=$params['customer']->id;
            $customer=$params['customer'];
            $order=$params['order'];
            $totalgastado=Db::getInstance()->getValue(
                sprintf(
                    "SELECT SUM(total_paid) FROM ps_orders WHERE id_customer = %d", 
                    (int)pSQL($customer_id)
                )
            );
            $totalacumulado=Db::getInstance()->getValue(
                sprintf(
                    "SELECT SUM(cantidadgastada) FROM ps_cuponglobal WHERE id_customer = %d", 
                    (int)pSQL($customer_id)
                )
            );
            $totalgastado_ = $totalgastado - $totalacumulado;

            $minimorequerido = Configuration::get('MINIMOREQUERIDO');

            if ($totalgastado_ > $minimorequerido) {
                $cartRuleObj = new CartRule();
                $cartRuleObj->date_from = date('Y-m-d H:i:s');
                $cartRuleObj->date_to = '2046-12-12 00:00:00';
                $cartRuleObj->name[Configuration::get('PS_LANG_DEFAULT')] = 'Cupon descuento';
                $cartRuleObj->quantity = 1;
                $code = Tools::passwdGen();
                while (CartRule::cartRuleExists($code)) { // let's make sure there is no duplicate
                    $code = Tools::passwdGen();
                }
                $cartRuleObj->code = $code;
                $cartRuleObj->quantity_per_user = 1;
                $cartRuleObj->reduction_percent = 20;
                $cartRuleObj->reduction_amount = 0;
                $cartRuleObj->free_shipping = 0;
                $cartRuleObj->active = 1;
                $cartRuleObj->minimum_amount = 0;
                $cartRuleObj->id_customer = $customer_id;
                $cartRuleObj->add();

                Db::getInstance()->insert('cuponglobal', array(
                    'cantidadgastada'=>$order->total_paid,
                    'cuponcode'=>$code,
                    'id_customer'=>$customer_id,
                    'email_customer'=>$customer->email,
                    'firstname_customer'=>$customer->firstname,
                    'lastname_customer'=>$customer->lastname,
                ));

                Mail::Send(
                    $this->context->language->id,
                    'coupon',
                    Mail::l('Cupon descuento'),
                    array(
                        '{firstname}' => $customer->firstname,
                        '{lastname}' => $customer->lastname,
                        '{email}' => $customer->email,
                        '{passwd}' => Tools::getValue('passwd'),
                        '{coupon}' => $code,
                    ),
                    $customer->email,
                    $customer->firstname.' '.$customer->lastname,
                    Configuration::get('PS_SHOP_EMAIL'),
                    Configuration::get('PS_SHOP_NAME'),
                    null,
                    true,
                    _PS_ROOT_DIR_.'/modules/emailcustomer/mails',
                    false,
                    null,
                    null,
                    null
                );
                //Db::getInstance()->execute(sprintf("INSERT INTO ps_cuponglobal(cantidadgastada, cuponcode, id_customer, email_customer, firstname_customer, lastname_customer) VALUES(%d, '%s', %d, '%s', '%s', '%s')"), (float)pSQL($order->total_paid), (string)pSQL($code), (int)pSQL($customer_id), (string)pSQL($customer->email), (string)pSQL($customer->firstname), (string)pSQL($customer->lastname));
                //Db::getInstance()->execute("INSERT INTO ps_cuponglobal(cantidadgastada, cuponcode, id_customer, email_customer, firstname_customer, lastname_customer) VALUES(".$order->total_paid.", '".$code."', ".$customer_id.", '".$customer->email."', '".$customer->firstname."', '".$customer->lastname."')");
            } else {
                return 'Error en el cupon global';
            }
        } else if ($numrows > 0) {
            //cupones para usuarios registrados asignandolos individualmente
            $id_customer=$this->context->customer->id;

            $moneydiscount=Db::getInstance()->executeS("SELECT `moneydiscount` from `"._DB_PREFIX_."emailcustomer` WHERE `user_id`=".$id_customer."");
            $discount=Db::getInstance()->executeS("SELECT `discount` from `"._DB_PREFIX_."emailcustomer` WHERE `user_id`=".$id_customer."");
            $codediscount=Db::getInstance()->executeS("SELECT `codediscount` from `"._DB_PREFIX_."emailcustomer` WHERE `user_id`=".$id_customer."");
            //$sumpaid=Db::getInstance()->executeS("SELECT SUM(`total_paid`) AS `sumpaid` from `"._DB_PREFIX_."orders` WHERE `id_customer`=".$id_customer." && `current_state`=2");
            $sumpaid=Db::getInstance()->getValue(sprintf("SELECT SUM(`total_paid`) AS `sumpaid` from `"._DB_PREFIX_."orders` WHERE `id_customer`= %d && `current_state`=2"), (int)pSQL($id_customer));

            if ($sumpaid >= $moneydiscount) {
                $cr = new CartRule();
                $cr->date_from = date('Y-m-d H:i:s');
                $cr->date_to = '2050-12-31 00:00:00';
                $cr->name[Configuration::get('PS_LANG_DEFAULT')] = 'Descuento';
                $cr->quantity = 1;
                $cr->codediscount= $codediscount;
                $cr->discount = $discount;
                $cr->free_shipping = false;
                $cr->active = true;
                $cr->id_customer = $this->context->customer->id;
                $cr->add();

                Mail::Send(
                    (int)(Configuration::get('PS_LANG_DEFAULT')),
                    'descuento',
                    Mail::l('Cupon descuento'),
                    array(
                        '{firstname}'=>$this->context->customer->firstname,
                        '{lastname}'=>$this->context->customer->lastname,
                        '{codediscount}'=>$codediscount,
                    ),
                    $this->context->customer->email,
                    null,
                    Configuration::get('PS_SHOP_EMAIL'),
                    Configuration::get('PS_SHOP_NAME'),
                    null,
                    true,
                    _PS_ROOT_DIR_.'/modules/emailcustomer/mails',
                    false,
                    null,
                    null,
                    null
                );
            } else {
                Mail::Send(
                    (int)(Configuration::get('PS_LANG_DEFAULT')),
                    'contact',
                    Mail::l('Total gastado'),
                    array(
                        '{firstname}'=>$this->context->customer->firstname,
                        '{lastname}'=>$this->context->customer->lastname,
                        '{sumpaid}'=>$sumpaid,
                    ),
                    $this->context->customer->email,
                    null,
                    Configuration::get('PS_SHOP_EMAIL'),
                    Configuration::get('PS_SHOP_NAME'),
                    null,
                    true,
                    _PS_ROOT_DIR_.'/modules/emailcustomer/mails',
                    false,
                    null,
                    null
                );
            }
        }
    }

    public function hookActionFrontControllerSetMedia($params)
    {
        $this->context->controller->addCSS($this->_path."/views/css/scratch.scs", "all");
        $this->context->controller->addJS($this->_path."/views/js/scratch.js");
    }

    public function hookDisplayOrderConfirmation()
    {
        return $this->l('Desliza hacia abajo para conseguir tu rasca y gana');
    }

    public function hookDisplayOrderConfirmation1($params)
    {
        $customer_id=$params['cart']->id_customer;
        $coupon=Db::getInstance()->executeS("SELECT cuponcode, MAX(id) FROM ps_cuponglobal WHERE id_customer= ".$customer_id."");
        $this->context->smarty->assign('coupon',$coupon); 
        $this->context->smarty->assign(array(
            'urlcss'=>$this->_path.'/views/css/scratch.scss',
            'urljs'=>$this->_path.'/views/js/scratch.js',
        ));
        return $this->display(__FILE__,'views/templates/hook/scratch.tpl');
    }
} 
