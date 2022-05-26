<?php

/**
* 2007-2022 PrestaShop
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
*  @copyright 2007-2022 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if(!class_exists('MspStorage'));
    require_once _PS_MODULE_DIR_.'myselphone/classes/MspStorage.php';

use Viaziza\Myselphone\Classes\Storage;

class AdminMspStorageController extends ModuleAdminController {

    public function __construct()
    {
        $this->table = 'vz_storage';
        $this->className = 'MspStorage';
        $this->lang = true;
        $this->bootstrap = true;

        $this->deleted = false;
        $this->allow_export = true;
        $this->list_id = 'vz_storage';
        $this->identifier = 'id_vz_storage';
        $this->_defaultOrderBy = 'id_vz_storage';
        $this->_defaultOrderWay = 'ASC';
        $this->context = Context::getContext();

        $this->addRowAction('edit');
        $this->addRowAction('delete'); 
        
        parent::__construct();

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected', [], 'Modules.Boixmdf.Adminboixequipecontroller.php'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?', [], 'Modules.Boixmdf.Adminboixequipecontroller.php')
            )
        );
        

        $this->fields_list = array(
            'id_vz_storage'=>array(
                'title' => $this->l('ID', [], 'Modules.Boixmdf.Adminboixequipecontroller.php'),
                'align'=>'center',
                'class'=>'fixed-width-xs'
            ),
            'name'=>array(
                'title'=>$this->l('Capacité', [], 'Modules.Boixmdf.Adminboixequipecontroller.php'),
                'width'=>'auto'
            ),
            'active' => array(
                'title' => $this->l('Enabled', [], 'Modules.Boixmdf.Adminboixequipecontroller.php'),
                'active' =>'status',
                'type' =>'bool',
                'align' =>'center',
                'class' =>'fixed-width-xs',
                'orderby' => false,
            ),
            'date_add'=>array(
                'title'=>$this->l('Date', [], 'Modules.Boixmdf.Adminboixequipecontroller.php'),
                'align' => 'text-left',
                'type' => 'datetime',
                'class' => 'fixed-width-lg',
                'filter_key' => 'a!date_add',
            ),
        );
    }
    

    public function renderForm()
    {
        if (!($storage = $this->loadObject(true))) {
            return;
        }
        
        $this->fields_form = array(
            'tinymce' => true,
            'legend' => array(
                'title' => $this->l('Storage', [], 'Modules.Boixmdf.Adminboixequipecontroller.php'),
                'icon' => 'icon-certificate'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Nom', [], 'Modules.Boixmdf.Adminboixequipecontroller.php'),
                    'name' => 'name',
                    'col' => 4,
                    'lang' => true,
                    'required' => true,
                    'hint' => $this->l('Invalid characters:').' &lt;&gt;;=#{}'
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Enable', [], 'Modules.Boixmdf.Adminboixequipecontroller.php'),
                    'name' => 'active',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled', [], 'Modules.Boixmdf.Adminboixequipecontroller.php')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled', [], 'Modules.Boixmdf.Adminboixequipecontroller.php')
                        )
                    )
                )
            )
        );

        if (!($storage = $this->loadObject(true))) {
            return;
        }


        $this->fields_form['submit'] = array(
            'title' => $this->l('Save', [], 'Modules.Boixmdf.Adminboixequipecontroller.php')
        );

        foreach ($this->_languages as $language) {
            $this->fields_value['name_'.$language['id_lang']] = htmlentities(Tools::stripslashes($this->getFieldValue(
                $storage,
                'name',
                $language['id_lang']
            )), ENT_COMPAT, 'UTF-8');
        }

        return parent::renderForm();
    }

    

    public function l($string, $params = [], $domaine = 'Modules.Boixmdf.Adminboixequipecontroller.php', $local = null){
        if(_PS_VERSION_ >= '1.7'){
            return $this->module->getTranslator()->trans($string, $params, $domaine, $local);
        }else{
            return parent::l($string, null, false, true);
        }
    }
}
