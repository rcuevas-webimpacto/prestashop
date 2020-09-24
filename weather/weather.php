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

use GeoIp2\Record\City;

require_once("vendor/autoload.php");

if (!defined('_PS_VERSION_')) {
    exit;
}

class Weather extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'weather';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'rcuevas-webimpacto';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('el Tiempo');
        $this->description = $this->l('Modulo para mostrar el tiempo en tu pagina web');

        $this->confirmUninstall = $this->l('Está seguro que desea desinstalarlo?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        //Configuration::updateValue('WEATHER_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayNav1');
    }

    public function uninstall()
    {
        //Configuration::deleteByName('WEATHER_LIVE_MODE');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitWeatherModule')) == true) {
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
        $helper->submit_action = 'submitWeatherModule';
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
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type'=>'text',
                        'label'=>$this->l('Clave de tu API'),
                        'name'=> 'APIKEY',
                        'desc'=> $this->l('Introduce la clave alfanumerica de tu cuenta de https://home.openweathermap.org/api_keys')
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Guardar'),
                    'name'=> 'submitForm',

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
            'WEATHER_LIVE_MODE' => Configuration::get('WEATHER_LIVE_MODE', true),
            'APIKEY'=> Configuration::get('APIKEY',true),
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

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookDisplayHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function getIP()
    {
        if (isset($_SERVER["HTTP_CLIENT_IP"]))
        {
            return $_SERVER["HTTP_CLIENT_IP"];
        }
        elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
        {
            return $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
        elseif (isset($_SERVER["HTTP_X_FORWARDED"]))
        {
            return $_SERVER["HTTP_X_FORWARDED"];
        }
        elseif (isset($_SERVER["HTTP_FORWARDED_FOR"]))
        {
            return $_SERVER["HTTP_FORWARDED_FOR"];
        }
        elseif (isset($_SERVER["HTTP_FORWARDED"]))
        {
            return $_SERVER["HTTP_FORWARDED"];
        }
        else
        {
            return $_SERVER["REMOTE_ADDR"];
        }

    }

    public function hookDisplayNav1()
    {
        /* Place your code here. */
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');

        //UBICACION 
        // This creates the Reader object, which should be reused across lookups.
        $reader = new GeoIp2\Database\Reader("app/Resources/geoip/GeoLite2-City.mmdb");
        //Replace "city" with the appropriate method for your database, e.g., "country".
        $record = $reader->city($this->getIP());
        $defcity=new City($record);
        $cityName=print($defcity->city->name); // 'Minneapolis'

        //MOSTRAR API
        $handle = curl_init();
        $url = "http://api.openweathermap.org/data/2.5/weather?q=".$cityName."&appid=eea09d8df75afa33278174460f88bd02";
        
        // Set the url
        curl_setopt($handle, CURLOPT_URL, $url);
        // Set the result output to be a string.
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        
        $output = curl_exec($handle);
        curl_close($handle);
        $data= json_decode($output);
        $this->smarty->assign(array(
            'temp' => $data->main->temp,
            'humidity' => $data->main->humidity,
            'pressure' => $data->main->pressure
        ));

        return $this->display(__FILE__, 'weather.tpl');
    }
}