<?php
/**
 * @category    Module / customizations
 * @author      Adrien THIERRY www.tafftaff.fr
 * @copyright   2021 Adrien THIERRY
 * @version     1.0
 * @link        https://www.tafftaff.fr
 * @since       File available since Release 1.0
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Tt_custominvoice extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'tt_custominvoice';
        $this->tab = 'billing_invoicing';
        $this->version = '1.0.1';
        $this->author = 'Adrien THIERRY - tafftaff';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Invoice Custom ');
        $this->description = $this->l('Add content on your invoice with some conditions');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('TT_CUSTOMINVOICE_SENTENCE_UK', false);
        include(dirname(__FILE__) . '/sql/install.php');

        return parent::install() &&
            $this->registerHook('displayPDFInvoice') &&
            $this->registerHook('actionProductUpdate');
    }

    public function uninstall()
    {
        include(dirname(__FILE__) . '/sql/uninstall.php');
        Configuration::deleteByName('TT_CUSTOMINVOICE_SENTENCE_UK');
        Configuration::deleteByName('TT_CUSTOMINVOICE_SENTENCE_COUNTRY');
        Configuration::deleteByName('TT_CUSTOMINVOICE_SENTENCE_MODE');
        Configuration::deleteByName('TT_CUSTOMINVOICE_SENTENCE_TITLE');


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
        if (((bool)Tools::isSubmit('submitTt_custominvoiceModule')) == true) {
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
        $helper->submit_action = 'submitTt_custominvoiceModule';
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

    private function getCountries($idShop, $idLang)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
        SELECT c.id_country,c.iso_code, cl.name
        FROM `' . _DB_PREFIX_ . 'country` c
        LEFT JOIN `' . _DB_PREFIX_ . 'country_shop` cs ON (cs.`id_country`= c.`id_country`)
        LEFT JOIN `' . _DB_PREFIX_ . 'country_lang` cl ON (c.`id_country` = cl.`id_country` AND cl.`id_lang` = ' . (int) $idLang . ')
        WHERE active = 1 AND  `id_shop` = ' . (int) $idShop);
    }
    protected function getConfigForm()
    {

      $switch = array(
            array(
                'id' => 'active_on',
                'value' => '1',
                'label' => $this->l('Yes')
            ),
            array(
                'id' => 'active_off',
                'value' => '0',
                'label' => $this->l('No')
            )
        );
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Tab Title'),
                        'name' => 'TT_CUSTOMINVOICE_SENTENCE_TITLE',
                        'label' => $this->l('Tab Title'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter your sentence to show on invoice'),
                        'name' => 'TT_CUSTOMINVOICE_SENTENCE_UK',
                        'label' => $this->l('Free Text'),
                    ),
                    
                     array(
                        'col' => 3,
                        'type' => 'select',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter your sentence to show on invoice'),
                        'name' => 'TT_CUSTOMINVOICE_SENTENCE_COUNTRY',
                        'label' => $this->l('COUNTRY TO DISPLAY'),
                        'options' => array(
                            'id' => 'iso_code',
                            'query' => $this->getCountries(Context::getContext()->shop->id,Context::getContext()->language->id),
                            'name' => 'name'
                            ),
                    ),
                     array(
                        'col' => 3,
                        'type' => 'switch',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Attention le titre ne sera pas utilisé'),
                        'name' => 'TT_CUSTOMINVOICE_SENTENCE_MODE',
                        'label' => $this->l('Mettre le texte en note'),
                        'is_bool' => true,
                        'values' => $switch
                    ),
                ),


                'submit' => array(
                    'title' => $this->l('Save'),
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
            'TT_CUSTOMINVOICE_SENTENCE_UK' => Configuration::get('TT_CUSTOMINVOICE_SENTENCE_UK'),
            'TT_CUSTOMINVOICE_SENTENCE_COUNTRY' => Configuration::get('TT_CUSTOMINVOICE_SENTENCE_COUNTRY'),
            'TT_CUSTOMINVOICE_SENTENCE_MODE' => Configuration::get('TT_CUSTOMINVOICE_SENTENCE_MODE'),
            'TT_CUSTOMINVOICE_SENTENCE_TITLE' => Configuration::get('TT_CUSTOMINVOICE_SENTENCE_TITLE'),
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

    public function hookDisplayPDFInvoice($params)
    {

        $id_order = $params['object']->id_order;
        $id_order_invoice = $params['object']->id;
        $order_invoice = new OrderInvoice($id_order_invoice);
        $order = new Order($id_order);
        $address = new Address($order->id_address_delivery);
        $country = new Country($address->id_country);
        $products = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
        SELECT od.product_name,od.product_reference,p.hscode
        FROM `' . _DB_PREFIX_ . 'order_detail` od
        LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON (p.id_product = od.product_id)
        WHERE od.`id_order` = ' . (int) $order->id);


        if ($country->iso_code == Configuration::get('TT_CUSTOMINVOICE_SENTENCE_COUNTRY')){ // On vérifie le pays
            if (Configuration::get('TT_CUSTOMINVOICE_SENTENCE_MODE')){ // ON ajoute la note
                if (false === stristr($order_invoice->note,Configuration::get('TT_CUSTOMINVOICE_SENTENCE_UK'))){
                    $order_invoice->note = $order_invoice->note.'<br>'.Configuration::get('TT_CUSTOMINVOICE_SENTENCE_UK');
                    $order_invoice->update();
                    $order_invoice->save();
                    return;
                }
                else{
                    return;
                }
            }
            else{ // on le met sur le hook
                $title = Configuration::get('TT_CUSTOMINVOICE_SENTENCE_TITLE');
                $text = Configuration::get('TT_CUSTOMINVOICE_SENTENCE_UK');
                $this->context->smarty->assign([
                'title' => $title,
                'text' => $text,
                'productsdetails' => $products
                ]);
                return $this->display(__FILE__, 'hookDisplayPDFInvoice.tpl');
                
            }
        }
        else{
            return;
        } 
    }
    public function hookDisplayAdminProductsMainStepLeftColumnMiddle($params)
    {
        $id = (int)$params['id_product'];
        if (empty($id)) {
            return '';
        }
        $product = new Product($id);
        $this->context->smarty->assign('hscode', $product->hscode);
        return $this->display(__FILE__, 'views/templates/admin/create.tpl');
    }

    public function hookActionProductUpdate($params)
    {
        $id = (int)Tools::getValue('id_product');
        $product = new Product($id);
        if((int)Tools::getValue('hs_code') != $product->hscode) {
            $product->hscode = (int)Tools::getValue('hs_code');
            $product->save();
        }
    }
}
