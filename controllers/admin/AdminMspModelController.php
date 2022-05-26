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

if(!class_exists('MspModel'));
    require_once _PS_MODULE_DIR_.'myselphone/classes/MspModel.php';

use Viaziza\Myselphone\Classes\Model;
use Viaziza\Myselphone\Classes\Image;
use Viaziza\Myselphone\Classes\Color;
use Viaziza\Myselphone\Classes\Storage;

class AdminMspModelController extends ModuleAdminController {

    public function __construct()
    {
        $this->table = 'vz_model';
        $this->className = 'MspModel';
        $this->lang = true;
        $this->bootstrap = true;

        $this->deleted = false;
        $this->allow_export = true;
        $this->list_id = 'vz_model';
        $this->identifier = 'id_vz_model';
        $this->_defaultOrderBy = 'id_vz_model';
        $this->_defaultOrderWay = 'ASC';
        $this->context = Context::getContext();

        $this->addRowAction('edit');
        $this->addRowAction('delete');
        

        $this->_select .="m.name manufacturer";
        $this->_join .=" LEFT JOIN `"._DB_PREFIX_."manufacturer` m on m.id_manufacturer = a.id_manufacturer";

        parent::__construct();

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected', [], 'Modules.Myselphone.Adminmspmodelcontroller.php'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?', [], 'Modules.Myselphone.Adminmspmodelcontroller.php')
            )
        );

        $manufactures = $this->getManufacturers();
        $manufactures_list = array();
        foreach ($manufactures as $manufacture) {
            $manufactures_list[$manufacture['id']] = $manufacture['name'];
        }
        

        $this->fields_list = array(
            'id_vz_model'=>array(
                'title' => $this->l('ID', [], 'Modules.Myselphone.Adminmspmodelcontroller.php'),
                'align'=>'center',
                'class'=>'fixed-width-xs'
            ),
            'manufacturer'=>array(
                'title'=>$this->l('Manufacturer', [], 'Modules.Myselphone.Adminmspmodelcontroller.php'),
                'type' => 'select',
                'list' => $manufactures_list,
                'filter_key' => 'a!id_store',
                'filter_type' => 'int',
                'order_key' => 'id_store'
            ),
            'name'=>array(
                'title'=>$this->l('Nom', [], 'Modules.Myselphone.Adminmspmodelcontroller.php'),
                'width'=>'auto'
            ),
            'weight'=>array(
                'title'=>$this->l('Weight', [], 'Modules.Myselphone.Adminmspmodelcontroller.php'),
                'width'=>'auto'
            ),
            'active' => array(
                'title' => $this->l('Enabled', [], 'Modules.Myselphone.Adminmspmodelcontroller.php'),
                'active' =>'status',
                'type' =>'bool',
                'align' =>'center',
                'class' =>'fixed-width-xs',
                'orderby' => false,
            ),
            'date_add'=>array(
                'title'=>$this->l('Date', [], 'Modules.Myselphone.Adminmspmodelcontroller.php'),
                'align' => 'text-left',
                'type' => 'datetime',
                'class' => 'fixed-width-lg',
                'filter_key' => 'a!date_add',
            ),
        );
    }

    public function getManufacturers() {
        $q = new DbQuery();
        $q->select('id_manufacturer id, name')
            ->from('manufacturer')
        ;

        return Db::getInstance()->executeS($q);
    }

    public function renderForm()
    {
        if (!($model = $this->loadObject(true))) {
            return;
        }

        $this->fields_form = array(
            'tinymce' => true,
            'legend' => array(
                'title' => $this->l('Model', [], 'Modules.Myselphone.Adminmspmodelcontroller.php'),
                'icon' => 'icon-certificate'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Nom du model', [], 'Modules.Myselphone.Adminmspmodelcontroller.php'),
                    'name' => 'name',
                    'col' => 4,
                    'lang' => true,
                    'required' => true,
                    'hint' => $this->l('Invalid characters:').' &lt;&gt;;=#{}'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Poid', [], 'Modules.Myselphone.Adminmspmodelcontroller.php'),
                    'name' => 'weight',
                    'col' => 4,
                    'required' => true,
                    'hint' => $this->l('Invalid characters:').' &lt;&gt;;=#{}'
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Manufacturer', [], 'Modules.Myselphone.Adminmspmodelcontroller.php'),
                    'name' => 'id_manufacturer',
                    'col' => 4,
                    'options'=> array(
                        'query'=> $this->getManufacturers(),
                        'id'=>'id',
                        'name'=>'name',
                    ),
                    'required' => false,
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Colors', [], 'Modules.Myselphone.Adminmspmodelcontroller.php'),
                    'name' => 'colors[]',
                    'multiple' => true,
                    'class' => 'chosen',
                    'col' => 8,
                    'options'=> array(
                        'query'=> Color::getColors(),
                        'id'=>'id_vz_color',
                        'name'=>'name',
                    ),
                    'required' => false,
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Storages', [], 'Modules.Myselphone.Adminmspmodelcontroller.php'),
                    'name' => 'storages[]',
                    'multiple' => true,
                    'class' => 'chosen',
                    'col' => 8,
                    'options'=> array(
                        'query'=> Storage::getStorages(),
                        'id'=>'id_vz_storage',
                        'name'=>'name',
                    ),
                    'required' => false,
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Description', [], 'Modules.Myselphone.Adminmspmodelcontroller.php'),
                    'name' => 'description',
                    'lang' => true,
                    'cols' => 60,
                    'rows' => 10,
                    'col' => 6,
                    'hint' => $this->l('Invalid characters:', [], 'Modules.Myselphone.Adminmspmodelcontroller.php').' &lt;&gt;;=#{}'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Position', [], 'Modules.Myselphone.Adminmspmodelcontroller.php'),
                    'name' => 'position',
                    'col' => 4,
                    'required' => true,
                    'hint' => $this->l('Invalid characters:').' &lt;&gt;;=#{}'
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Enable', [], 'Modules.Myselphone.Adminmspmodelcontroller.php'),
                    'name' => 'active',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled', [], 'Modules.Myselphone.Adminmspmodelcontroller.php')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled', [], 'Modules.Myselphone.Adminmspmodelcontroller.php')
                        )
                    )
                )
            )
        );

        if (!($model = $this->loadObject(true))) {
            return;
        }


        $this->fields_form['submit'] = array(
            'title' => $this->l('Save', [], 'Modules.Myselphone.Adminmspmodelcontroller.php')
        );
        if($model->id) {
            $colors = Model::getColors($model->id, $this->context->language->id);
            if($colors && is_array($colors) && !empty($colors)) {
                $this->fields_value['colors[]'] = array_map(function($a){
                    return $a['id_vz_color'];
                }, $colors);
            }

            $storages = Model::getStorages($model->id, $this->context->language->id);
            if($storages && is_array($storages) && !empty($storages)) {
                $this->fields_value['storages[]'] = array_map(function($a){
                    return $a['id_vz_storage'];
                }, $storages);
            }
            
        }
        

        foreach ($this->_languages as $language) {
            $this->fields_value['name_'.$language['id_lang']] = htmlentities(Tools::stripslashes($this->getFieldValue(
                $model,
                'name',
                $language['id_lang']
            )), ENT_COMPAT, 'UTF-8');

            $this->fields_value['description_'.$language['id_lang']] = htmlentities(Tools::stripslashes($this->getFieldValue(
                $model,
                'description',
                $language['id_lang']
            )), ENT_COMPAT, 'UTF-8');
        }

        return parent::renderForm();
    }

    public function postProcess()
    {
        $objet = parent::postProcess();
        if($objet && Validate::isLoadedObject($objet)){
            Model::attachColors($objet->id, Tools::getValue('colors'));
            Model::attachStorages($objet->id, Tools::getValue('storages'));
        }
        return $objet;
    }

    

    public function l($string, $params = [], $domaine = 'Modules.Myselphone.Adminmspmodelcontroller.php', $local = null){
        if(_PS_VERSION_ >= '1.7'){
            return $this->module->getTranslator()->trans($string, $params, $domaine, $local);
        }else{
            return parent::l($string, null, false, true);
        }
    }
}
