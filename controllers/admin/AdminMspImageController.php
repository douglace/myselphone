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

if(!class_exists('MspImage'));
    require_once _PS_MODULE_DIR_.'myselphone/classes/MspImage.php';

use Viaziza\Myselphone\Classes\Image;

class AdminMspImageController extends ModuleAdminController {

    public function __construct()
    {
        $this->table = 'vz_image';
        $this->className = 'MspImage';
        $this->lang = false;
        $this->bootstrap = true;

        $this->deleted = false;
        $this->allow_export = true;
        $this->list_id = 'vz_image';
        $this->identifier = 'id_vz_image';
        $this->_defaultOrderBy = 'id_vz_image';
        $this->_defaultOrderWay = 'ASC';
        $this->context = Context::getContext();

        $this->addRowAction('edit');
        $this->addRowAction('delete'); 
        
        $this->fieldImageSettings = array(
            'name' => 'avatar',
            'dir' => 'msp_model'
        );
        parent::__construct();

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected', [], 'Modules.Myselphone.Adminmspimagecontroller.php'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?', [], 'Modules.Myselphone.Adminmspimagecontroller.php')
            )
        );

        $this->_select .="ml.name model";
        $this->_join .=" LEFT JOIN `"._DB_PREFIX_."vz_model_lang` ml on ml.id_vz_model = a.id_vz_model and ml.id_lang=".$this->context->language->id;


        $models = $this->getModels();
        $models_list = array();
        foreach ($models as $model) {
            $models_list[$model['id']] = $model['name'];
        }

        

        $this->fields_list = array(
            'id_vz_image'=>array(
                'title' => $this->l('ID', [], 'Modules.Myselphone.Adminmspimagecontroller.php'),
                'align'=>'center',
                'class'=>'fixed-width-xs'
            ),
            'avatar' => array(
                'title' => $this->l('Image', [], 'Modules.Myselphone.Adminmspimagecontroller.php'),
                'image' => 'msp_model',
                'orderby' => false,
                'search' => false,
                'align' => 'center',
            ),
            'model'=>array(
                'title'=>$this->l('model', [], 'Modules.Myselphone.Adminmspimagecontroller.php'),
                'type' => 'select',
                'list' => $models_list,
                'filter_key' => 'a!id_vz_model',
                'filter_type' => 'int',
                'order_key' => 'id_vz_model'
            ),
            'name'=>array(
                'title'=>$this->l('Nom', [], 'Modules.Myselphone.Adminmspimagecontroller.php'),
                'width'=>'auto'
            ),
            'position'=>array(
                'title'=>$this->l('Position', [], 'Modules.Myselphone.Adminmspimagecontroller.php'),
                'width'=>'auto'
            ),
            'active' => array(
                'title' => $this->l('Enabled', [], 'Modules.Myselphone.Adminmspimagecontroller.php'),
                'active' =>'status',
                'type' =>'bool',
                'align' =>'center',
                'class' =>'fixed-width-xs',
                'orderby' => false,
            ),
        );
    }

    public function getModels() {
        $q = new DbQuery();
        $q->select('id_vz_model id, name')
            ->from('vz_model_lang')
            ->where('id_lang='.$this->context->language->id)
        ;

        return Db::getInstance()->executeS($q);
    }
    

    public function renderForm()
    {
        if (!($mspimage = $this->loadObject(true))) {
            return;
        }

        $image = Image::getImgPath(false).DIRECTORY_SEPARATOR.$mspimage->id.'.jpg';
        $image_url = ImageManager::thumbnail(
            $image,
            $this->table.'_'.(int)$mspimage->id.'.'.$this->imageType,
            350,
            $this->imageType,
            true,
            true
        );
        
        $image_size = file_exists($image) ? filesize($image) / 1000 : false;
        
        $this->fields_form = array(
            'tinymce' => true,
            'legend' => array(
                'title' => $this->l('Image', [], 'Modules.Myselphone.Adminmspimagecontroller.php'),
                'icon' => 'icon-certificate'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Nom', [], 'Modules.Myselphone.Adminmspimagecontroller.php'),
                    'name' => 'name',
                    'col' => 4,
                    'required' => true,
                    'hint' => $this->l('Invalid characters:').' &lt;&gt;;=#{}'
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Model', [], 'Modules.Myselphone.Adminmspimagecontroller.php'),
                    'name' => 'id_vz_model',
                    'col' => 4,
                    'options'=> array(
                        'query'=> $this->getModels(),
                        'id'=>'id',
                        'name'=>'name',
                    ),
                    'required' => false,
                ),
                array(
                    'type' => 'file',
                    'label' => $this->l('Avatar', [], 'Modules.Myselphone.Adminmspimagecontroller.php'),
                    'name' => 'avatar',
                    'image' => $image_url ? $image_url : false,
                    'size' => $image_size,
                    'display_image' => true,
                    'col' => 6,
                    'hint' => $this->l('Upload a mspimage logo from your computer.', [], 'Modules.Myselphone.Adminmspimagecontroller.php')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Position', [], 'Modules.Myselphone.Adminmspimagecontroller.php'),
                    'name' => 'position',
                    'col' => 4,
                    'required' => true,
                    'hint' => $this->l('Invalid characters:').' &lt;&gt;;=#{}'
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Enable', [], 'Modules.Myselphone.Adminmspimagecontroller.php'),
                    'name' => 'active',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled', [], 'Modules.Myselphone.Adminmspimagecontroller.php')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled', [], 'Modules.Myselphone.Adminmspimagecontroller.php')
                        )
                    )
                )
            )
        );

        if (!($mspimage = $this->loadObject(true))) {
            return;
        }


        $this->fields_form['submit'] = array(
            'title' => $this->l('Save', [], 'Modules.Myselphone.Adminmspimagecontroller.php')
        );
        

        return parent::renderForm();
    }

    

    public function l($string, $params = [], $domaine = 'Modules.Myselphone.Adminmspimagecontroller.php', $local = null){
        if(_PS_VERSION_ >= '1.7'){
            return $this->module->getTranslator()->trans($string, $params, $domaine, $local);
        }else{
            return parent::l($string, null, false, true);
        }
    }
}
