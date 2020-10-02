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

        return parent::install() &&
            $this->registerHook('actionValidateOrder');
    }

    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall() && $this->_uninstallSql();
        
    }

    protected function _uninstallSql(){
        $sqluninstall=Db::getInstance()->execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'emailcustomer');

        return $sqluninstall;
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
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        //
        $moneydiscount=(int)(Tools::getValue('moneydiscount'));
        $discount=(int)(Tools::getValue('discount'));
        $codediscount=(string)(Tools::getValue('codediscount'));
        $user_id=(int)(Tools::getValue('user_id'));
        $dbcontent=Db::getInstance()->executeS("SELECT `moneydiscount`,`discount`,`codediscount`,`user_id` from "._DB_PREFIX_."emailcustomer");
        $smarty=$this->smarty->assign('data',$dbcontent);
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
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(['user_id','moneydiscount','discount','codediscount']), /* Add values for your inputs */
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
                'title' => $this->l('Configuracion del cupon descuento'),
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

    // %percentage%

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

        $id_emailcustomer=(int)Tools::getValue('id_emailcustomer');
        $moneydiscount=(int)(Tools::getValue('moneydiscount'));
        $discount=(int)(Tools::getValue('discount'));
        $codediscount=(string)(Tools::getValue('codediscount'));
        $user_id=(int)(Tools::getValue('user_id'));

        if(Tools::isSubmit('guardar')){
            Db::getInstance()->insert('emailcustomer',array(
                'moneydiscount'=>$moneydiscount,
                'discount'=>$discount,
                'codediscount'=>$codediscount,
                'user_id'=>$user_id,
            ));
        }
    }

    public function hookActionValidateOrder($params)
    {
        $moneydiscount=(int)(Tools::getValue('moneydiscount'));
        $discount=(int)(Tools::getValue('discount'));
        $codediscount=(string)(Tools::getValue('codediscount'));
        $user_id=(int)(Tools::getValue('user_id'));

        $customer=$params['customer'];
        //$order=$params['order'];
        $shopemail=Configuration::get('PS_SHOP_EMAIL');
        $shopname=Configuration::get('PS_SHOP_NAME');
        $sumpaid=Db::getInstance()->executeS("SELECT SUM(`total_paid`) AS `sumpaid` from `"._DB_PREFIX_."orders` WHERE `id_customer`=3 && `current_state`=2");
        //$total_paid=$order->total_paid;
        $idLang=(int)(Configuration::get('PS_LANG_DEFAULT'));
        $subject=sprintf(Mail::l('Pedido n. %d cofirmado'), $params['order']->id);
        $templateVars=array(
            '{firstname}'=>$customer->firstname,
            '{lastname}'=>$customer->lastname,
            '{email}'=>$customer->email,
            '{sumpaid}'=>$sumpaid,
            '{shopname}'=>$shopname,
        );
        $templatePath=_PS_MAIL_DIR_;//_PS_ROOT_DIR_.'/modules/emailcustomer/mails/es/contact.html';

        if($sumpaid < $moneydiscount && $user_id==$customer->id){
            Mail::Send(
                $idLang, //defaut language id
                'contact', //email template file to be use
                $subject, //email subject
                $templateVars, //email vars
                $customer->email, //receiver email address
                NULL, //receiver name
                $shopemail, //from email address
                $shopname,  //from name
                array(
                    'Content-type: text/html; charset=iso-8859-1'."\r\n",
                    'MIME-Version: 1.0'."\r\n",
                    'contact'
                ), //file attachment
                true, //mode smtp
                $templatePath, //custom template path
                false, //die
                Configuration::get('PS_SHOP_ID') //shop id
            );
        }

        else if($sumpaid >= $moneydiscount && $user_id==$customer->id) {
            $cr = new CartRule();
            $cr->date_from = date('Y-m-d H:i:s');
            $cr->date_to = '2050-12-31 00:00:00';
            $cr->name[Configuration::get('PS_LANG_DEFAULT')] = 'Descuento';
            $cr->quantity = 1;
            $cr->codediscount= $codediscount;
            $cr->discount = $discount;
            $cr->free_shipping = false;
            $cr->active = true;
            $cr->id_customer = $customer->id;
            $cr->add();

            Mail::Send(
                $idLang, //defaut language id
                'descuento', //email template file to be use
                $subject, //email subject
                $templateVars, //email vars
                $customer->email, //receiver email address
                NULL, //receiver name
                $shopemail, //from email address
                $shopname,  //from name
                array(
                    'Content-type: text/html; charset=iso-8859-1'."\r\n",
                    'MIME-Version: 1.0'."\r\n",
                    'contact'
                ), //file attachment
                true, //mode smtp
                $templatePath, //custom template path
                false, //die
                Configuration::get('PS_SHOP_ID') //shop id
            );
        }
        else {
            return 'Error en el cupon';
        }

        /*$mensaje= '
        <!DOCTYPE html>
        <html>
            <head>
                <title>Pedido</title>
            </head>
            <body>
                <div class="row">
                    <div class="col-12">
                        <p>Hola {firstname} {lastname} has gastado {total} € en {shopname}</p>
                    </div>
                </div>
            </body>
        </html>
        ';
        $headers='MIME-Version: 1.0' . "\r\n";
        $headers .= 'From: '.$shopemail.'' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        mail($customer->email,$subject,$mensaje,$headers);*/
    }
}
