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

if(!class_exists('MspColor'));
    require_once _PS_MODULE_DIR_.'myselphone/classes/MspColor.php';

use Viaziza\Myselphone\Classes\Color;

class AdminMspColorController extends ModuleAdminController {

    public function __construct()
    {
        $this->table = 'vz_color';
        $this->className = 'MspColor';
        $this->lang = true;
        $this->bootstrap = true;

        $this->deleted = false;
        $this->allow_export = true;
        $this->list_id = 'vz_color';
        $this->identifier = 'id_vz_color';
        $this->_defaultOrderBy = 'id_vz_color';
        $this->_defaultOrderWay = 'ASC';
        $this->context = Context::getContext();

        $this->addRowAction('edit');
        $this->addRowAction('delete'); 
        
        $this->fieldImageSettings = array(
            'name' => 'avatar',
            'dir' => 'msp_color'
        );
        
        parent::__construct();

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected', [], 'Modules.Myselphone.AdminMspcolorcontroller.php'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?', [], 'Modules.Myselphone.AdminMspcolorcontroller.php')
            )
        );
        

        $this->fields_list = array(
            'id_vz_color'=>array(
                'title' => $this->l('ID', [], 'Modules.Myselphone.AdminMspcolorcontroller.php'),
                'align'=>'center',
                'class'=>'fixed-width-xs'
            ),
            'avatar' => array(
                'title' => $this->l('Image', [], 'Modules.Myselphone.AdminMspcolorcontroller.php'),
                'image' => 'msp_color',
                'orderby' => false,
                'search' => false,
                'align' => 'center',
            ),
            'name'=>array(
                'title'=>$this->l('Nom', [], 'Modules.Myselphone.AdminMspcolorcontroller.php'),
                'width'=>'auto'
            ),
            'value'=>array(
                'title'=>$this->l('value', [], 'Modules.Myselphone.AdminMspcolorcontroller.php'),
                'width'=>'auto',
                'callback' => 'displayColor'
            ),
            'active' => array(
                'title' => $this->l('Enabled', [], 'Modules.Myselphone.AdminMspcolorcontroller.php'),
                'active' =>'status',
                'type' =>'bool',
                'align' =>'center',
                'class' =>'fixed-width-xs',
                'orderby' => false,
            )
        );
    }

    public function displayColor($value, $row) {
        return "<span style='background-color:".$value."; display:block; width:40px; height:30px; border:2px solid #e7e7e7;'></span>";
    }

    public function getStores() {
        $q = new DbQuery();
        $q->select('id_store id, name')
            ->from('store_lang')
            ->where('id_lang='.$this->context->language->id)
        ;

        return Db::getInstance()->executeS($q);
    }

    public function renderForm()
    {
        if (!($color = $this->loadObject(true))) {
            return;
        }

        $image = Color::getImgPath(false).DIRECTORY_SEPARATOR.$color->id.'.jpg';
        $image_url = ImageManager::thumbnail(
            $image,
            $this->table.'_'.(int)$color->id.'.'.$this->imageType,
            350,
            $this->imageType,
            true,
            true
        );
        
        $image_size = file_exists($image) ? filesize($image) / 1000 : false;
        
        $this->fields_form = array(
            'tinymce' => true,
            'legend' => array(
                'title' => $this->l('Color', [], 'Modules.Myselphone.AdminMspcolorcontroller.php'),
                'icon' => 'icon-certificate'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Nom de la couleur', [], 'Modules.Myselphone.AdminMspcolorcontroller.php'),
                    'name' => 'name',
                    'col' => 4,
                    'lang' => true,
                    'required' => true,
                    'hint' => $this->l('Invalid characters:').' &lt;&gt;;=#{}'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Valeur', [], 'Modules.Myselphone.AdminMspcolorcontroller.php'),
                    'name' => 'value',
                    'col' => 4,
                    'required' => true,
                    'hint' => $this->l('Invalid characters:').' &lt;&gt;;=#{}'
                ),
                array(
                    'type' => 'file',
                    'label' => $this->l('Image', [], 'Modules.Myselphone.AdminMspcolorcontroller.php'),
                    'name' => 'avatar',
                    'image' => $image_url ? $image_url : false,
                    'size' => $image_size,
                    'display_image' => true,
                    'col' => 6,
                    'hint' => $this->l('Upload a color logo from your computer.', [], 'Modules.Myselphone.AdminMspcolorcontroller.php')
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Enable', [], 'Modules.Myselphone.AdminMspcolorcontroller.php'),
                    'name' => 'active',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled', [], 'Modules.Myselphone.AdminMspcolorcontroller.php')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled', [], 'Modules.Myselphone.AdminMspcolorcontroller.php')
                        )
                    )
                )
            )
        );

        if (!($color = $this->loadObject(true))) {
            return;
        }


        $this->fields_form['submit'] = array(
            'title' => $this->l('Save', [], 'Modules.Myselphone.AdminMspcolorcontroller.php')
        );

        foreach ($this->_languages as $language) {
            $this->fields_value['name_'.$language['id_lang']] = htmlentities(Tools::stripslashes($this->getFieldValue(
                $color,
                'name',
                $language['id_lang']
            )), ENT_COMPAT, 'UTF-8');
        }

        return parent::renderForm();
    }

    

    public function l($string, $params = [], $domaine = 'Modules.Myselphone.AdminMspcolorcontroller.php', $local = null){
        if(_PS_VERSION_ >= '1.7'){
            return $this->module->getTranslator()->trans($string, $params, $domaine, $local);
        }else{
            return parent::l($string, null, false, true);
        }
    }
}
