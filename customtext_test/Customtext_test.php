<?php
/**
* 2007-2015 PrestaShop
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
*  @copyright  2007-2015 PrestaShop SA

*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

require_once _PS_MODULE_DIR_.'customtext_test/classes/CustomTextTest.php';

class CustomText_Test extends Module implements WidgetInterface
{
    // Equivalent module on PrestaShop 1.6, sharing the same data
    const MODULE_16 = 'blockcmsinfo_test';

    private $templateFile;

    public function __construct()
    {
        $this->name = 'customtext_test';
        $this->author = 'rcuevas-webimpacto';
        $this->version = '4.1.0';
        $this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();

        Shop::addTableAssociation('info_test', array('type' => 'shop'));

        $this->displayName = $this->trans('Bloques de texto personalizados rcuevas', array(), 'Modules.Customtext_Test.Admin');
        $this->description = $this->trans('Integrates custom text blocks anywhere in your store front', array(), 'Modules.Customtext_Test.Admin');

        $this->ps_versions_compliancy = array('min' => '1.7.4.0', 'max' => _PS_VERSION_);

        $this->templateFile = 'module:customtext_test/customtext_test.tpl';
    }

    public function install()
    {
         // Remove 1.6 equivalent module to avoid DB issues
        if (Module::isInstalled(self::MODULE_16)) {
            return $this->installFrom16Version();
        }

        return $this->runInstallSteps()
            && $this->installFixtures();
    }

    public function runInstallSteps()
    {
        return parent::install()
            && $this->installDB()
            && $this->registerHook('displayHome')
            && $this->registerHook('displayFooterBefore')
            && $this->registerHook('actionShopDataDuplication');
    }

    public function installFrom16Version()
    {
        require_once _PS_MODULE_DIR_.$this->name.'/classes/MigrateDataTest.php';
        $migration = new MigrateDataTest();
        $migration->retrieveOldData();

        $oldModule = Module::getInstanceByName(self::MODULE_16);
        if ($oldModule) {
            $oldModule->uninstall();
        }
        return $this->uninstallDB()
            && $this->runInstallSteps()
            && $migration->insertData();
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->uninstallDB();
    }

    public function installDB()
    {
        $return = true;
        $return &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS`'._DB_PREFIX_.'info_test`(`id_info` INT UNSIGNED NOT NULL AUTO_INCREMENT,PRIMARY KEY(`id_info`))ENGINE='._MYSQL_ENGINE_.'DEFAULT CHARSET=utf8 ;');

        $return &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS`'._DB_PREFIX_.'info_test_shop`(`id_info` INT(10) UNSIGNED NOT NULL,`id_shop` INT(10) UNSIGNED NOT NULL,PRIMARY KEY (`id_info`,`id_shop`)) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 ;');

        $return &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'info_test_lang`(`id_info` INT UNSIGNED NOT NULL,`id_shop` INT(10) UNSIGNED NOT NULL,`id_lang` INT(10) UNSIGNED NOT NULL ,`text` text NOT NULL,PRIMARY KEY(`id_info`, `id_lang`,`id_shop`))ENGINE='._MYSQL_ENGINE_.'DEFAULT CHARSET=utf8 ;');
        return $return;
    }

    public function uninstallDB($drop_table = true)
    {
        $ret = true;
        if ($drop_table) {
            $ret &= Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'info_test`')
                && Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'info_test_shop`')
                && Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'info_test_lang`');
        }

        return $ret;
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('savecustomtext_test')) {
            if (!Tools::getValue('text_'.(int)Configuration::get('PS_LANG_DEFAULT'), false)) {
                $output = $this->displayError($this->trans('Please fill out all fields.', array(), 'Admin.Notifications.Error')) . $this->renderForm();
            } else {
                $update = $this->ProcessSaveCustomText_Test();

                if (!$update) {
                    $output = '<div class="alert alert-danger conf error">'
                        .$this->trans('An error occurred on saving.', array(), 'Admin.Notifications.Error')
                        .'</div>';
                }

                $this->_clearCache($this->templateFile);
            }
        }

        return $output.$this->renderForm();
    }

    public function ProcessSaveCustomText_Test()
    {
        $shops = Tools::getValue('checkBoxShopAsso_configuration', array($this->context->shop->id));
        $text = array();
        $languages = Language::getLanguages(false);

        foreach ($languages as $lang) {
            $text[$lang['id_lang']] = Tools::getValue('text_'.$lang['id_lang']);
        }

        $saved = true;
        foreach ($shops as $shop) {
            Shop::setContext(Shop::CONTEXT_SHOP, $shop);
            $info_test = new CustomTextTest(Tools::getValue('id_info', 1));
            $info_test->text = $text;
            $saved &= $info_test->save();
        }

        return $saved;
    }

    protected function renderForm()
    {
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        $fields_form = array(
            'tinymce' => true,
            'legend' => array(
                'title' => $this->trans('CMS block', array(), 'Modules.Customtext_Test.Admin'),
            ),
            'input' => array(
                'id_info' => array(
                    'type' => 'hidden',
                    'name' => 'id_info'
                ),
                'content' => array(
                    'type' => 'textarea',
                    'label' => $this->trans('Text block', array(), 'Modules.Customtext_Test.Admin'),
                    'lang' => true,
                    'name' => 'text',
                    'cols' => 40,
                    'rows' => 10,
                    'class' => 'rte',
                    'autoload_rte' => true,
                ),
            ),
            'submit' => array(
                'title' => $this->trans('Save', array(), 'Admin.Actions'),
            ),
            'buttons' => array(
                array(
                    'href' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
                    'title' => $this->trans('Back to list', array(), 'Admin.Actions'),
                    'icon' => 'process-icon-back'
                )
            )
        );

        if (Shop::isFeatureActive() && Tools::getValue('id_info') == false) {
            $fields_form['input'][] = array(
                'type' => 'shop',
                'label' => $this->trans('Shop association', array(), 'Admin.Global'),
                'name' => 'checkBoxShopAsso_theme'
            );
        }


        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = 'customtext_test';
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        foreach (Language::getLanguages(false) as $lang) {
            $helper->languages[] = array(
                'id_lang' => $lang['id_lang'],
                'iso_code' => $lang['iso_code'],
                'name' => $lang['name'],
                'is_default' => ($default_lang == $lang['id_lang'] ? 1 : 0)
            );
        }

        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
        $helper->toolbar_scroll = true;
        $helper->title = $this->displayName;
        $helper->submit_action = 'savecustomtext_test';

        $helper->fields_value = $this->getFormValues();

        return $helper->generateForm(array(array('form' => $fields_form)));
    }

    public function getFormValues()
    {
        $fields_value = array();
        $idShop = $this->context->shop->id;
        $idInfo = CustomTextTest::getCustomTextIdByShop($idShop);

        Shop::setContext(Shop::CONTEXT_SHOP, $idShop);
        $info_test = new CustomTextTest((int)$idInfo);

        $fields_value['text'] = $info_test->text;
        $fields_value['id_info'] = $idInfo;

        return $fields_value;
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        if (!$this->isCached($this->templateFile, $this->getCacheId('customtext_test'))) {
            $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
        }

        return $this->fetch($this->templateFile, $this->getCacheId('customtext_test'));
    }
    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'info_test_lang`
            WHERE `id_lang` = '.(int)$this->context->language->id.' AND  `id_shop` = '.(int)$this->context->shop->id;

        return array(
            'cms_infos' => Db::getInstance()->getRow($sql),
        );
    }

    public function installFixtures()
    {
        $return = true;
        $tabTexts = array(
            array(
                'text' => '<h2>Custom Text Block 1</h2>
<p><strong class="dark">Lorem ipsum dolor sit amet conse ctetu 1</strong></p>
<p>1 Sit amet conse ctetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit.</p>'
            ),
        );

        $shopsIds = Shop::getShops(true, null, true);
        $languages = Language::getLanguages(false);
        $text = array();

        foreach ($tabTexts as $tab) {
            $info_test = new CustomTextTest();
            foreach ($languages as $lang) {
                $text[$lang['id_lang']] = $tab['text'];
            }
            $info_test->text = $text;
            $return &= $info_test->add();
        }

        if ($return && sizeof($shopsIds) > 1) {
            foreach ($shopsIds as $idShop) {
                Shop::setContext(Shop::CONTEXT_SHOP, $idShop);
                $info_test->text = $text;
                $return &= $info_test->save();
            }
        }

        return $return;
    }

    /**
     * Add CustomText when adding a new Shop
     *
     * @param array $params
     */
    public function hookActionShopDataDuplication($params)
    {
        if ($infoId = CustomTextTest::getCustomTextIdByShop($params['old_id_shop'])) {
            Shop::setContext(Shop::CONTEXT_SHOP, $params['old_id_shop']);
            $oldInfo = new CustomTextTest($infoId);

            Shop::setContext(Shop::CONTEXT_SHOP, $params['new_id_shop']);
            $newInfo = new CustomTextTest($infoId, null, $params['new_id_shop']);
            $newInfo->text = $oldInfo->text;

            $newInfo->save();
        }
    }
}
