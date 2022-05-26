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

namespace Viaziza\Myselphone;


use Language;
use Context;
use Tab;
use Db;

class Repository
{

    public static $img_color_dir = _PS_IMG_DIR_.'msp_color';
    public static $img_color_front = _PS_IMG_.'msp_color';

    public static $img_model_img_dir = _PS_IMG_DIR_.'msp_model';
    public static $img_model_img_front = _PS_IMG_.'msp_model';

    /**
     * Module
     * @param \Module $module
     */
    protected $module;

    /**
     * @param array $tabs
     */
    protected $tabs;

    /**
     * @param \Module $module
     */
    public function __construct($module)
    {
        $this->module = $module;
        $this->tabs = $this->module->tabs;
    }

    /**
     * Installer le module
     */
    public function install()
    {
        return $this->installDatabase() &&
        $this->installTab(true) &&
        $this->installFolder() &&
        $this->registerHooks();
    }

    public function uninstall()
    {
        return $this->unInstallDatabase() && $this->installTab(false);
    }

    /**
     * Installer le dossier dans le repertoire img
     */
    protected function installFolder()
    {
        if (!file_exists(self::$img_color_dir)) {
            $a = @mkdir(self::$img_color_dir, 0777);
            $a &= @chmod(self::$img_color_dir, 0777);
        }

        if (!file_exists(self::$img_model_img_dir)) {
            $a = @mkdir(self::$img_model_img_dir, 0777);
            $a &= @chmod(self::$img_model_img_dir, 0777);
        }

        return true;
    }

    

    /**
     * Installer un nouvelle onglet en admin
     */
    public function installTab($install = true)
    {
        if ($install) {
            $languages = Language::getLanguages();

            foreach ($this->tabs as $t) {
                $exist = Tab::getIdFromClassName($t['class_name']);
                if(!$exist) { 
                    $tab = new Tab();
                    $tab->module = $this->module->name;
                    $tab->class_name = $t['class_name'];
                    $tab->id_parent = Tab::getIdFromClassName($t['parent']);

                    foreach ($languages as $language) {
                        $tab->name[$language['id_lang']] = $t['name'];
                    }
                    $tab->save();
                }
                
            }
            return true;
        } else {
            foreach ($this->tabs as $t) {
                $id = Tab::getIdFromClassName($t['class_name']);
                if ($id) {
                    $tab = new Tab($id);
                    $tab->delete();
                }
            }

            return true;
        }
    }

    /**
     * Installer la base de donné
     */
    protected function installDatabase()
    {
        $sql = array();

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'vz_storage` (
            `id_vz_storage` INT(11) NOT NULL AUTO_INCREMENT,
            `active` INT(1) DEFAULT 1,
            `deleted` INT(1) DEFAULT 0,
            `date_add` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `date_upd` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (`id_vz_storage`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'vz_storage_lang` (
            `id_vz_storage` INT(11) NOT NULL,
            `id_lang` INT(11) NOT NULL,
            `name` VARCHAR(200),
            PRIMARY KEY  (`id_vz_storage`, `id_lang`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'vz_color` (
            `id_vz_color` INT(11) NOT NULL AUTO_INCREMENT,
            `value` VARCHAR(200),
            `active` INT(1) DEFAULT 1,
            `deleted` INT(1) DEFAULT 0,
            `date_add` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `date_upd` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (`id_vz_color`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'vz_color_lang` (
            `id_vz_color` INT(11) NOT NULL,
            `id_lang` INT(11) NOT NULL,
            `name` VARCHAR(200),
            PRIMARY KEY  (`id_vz_color`, `id_lang`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'vz_model` (
            `id_vz_model` INT(11) NOT NULL AUTO_INCREMENT,
            `id_manufacturer` INT(11) NOT NULL,
            `position` INT(10) DEFAULT 0,
            `weight` DECIMAL(20,6),
            `active` INT(1) DEFAULT 1,
            `deleted` INT(1) DEFAULT 0,
            `date_add` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `date_upd` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (`id_vz_model`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'vz_model_storage` (
            `id_vz_model` INT(11) NOT NULL,
            `id_vz_storage` INT(11) NOT NULL,
            `position` INT(10) DEFAULT 0,
            PRIMARY KEY  (`id_vz_model`, `id_vz_storage`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'vz_model_color` (
            `id_vz_model` INT(11) NOT NULL,
            `id_vz_color` INT(11) NOT NULL,
            `position` INT(10) DEFAULT 0,
            PRIMARY KEY  (`id_vz_model`, `id_vz_color`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'vz_model_lang` (
            `id_vz_model` INT(11) NOT NULL,
            `id_lang` INT(11) NOT NULL,
            `name` VARCHAR(200),
            `description` VARCHAR(200),
            PRIMARY KEY  (`id_vz_model`, `id_lang`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'vz_image` (
            `id_vz_image` INT(11) NOT NULL AUTO_INCREMENT,
            `id_vz_model` INT(11) NOT NULL,
            `position` INT(10) DEFAULT 0,
            `name` VARCHAR(200),
            `active` INT(1) DEFAULT 1,
            `deleted` INT(1) DEFAULT 0,
            `date_add` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `date_upd` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (`id_vz_image`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        
        foreach ($sql as $query) {
            if (Db::getInstance()->execute($query) == false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Désinstallé la base de donné
     */
    protected function unInstallDatabase()
    {
        $sql = array();
        
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'vz_model_storage`';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'vz_model_color`';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'vz_storage`';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'vz_storage_lang`';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'vz_color`';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'vz_color_lang`';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'vz_model`';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'vz_model_lang`';
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'vz_image`';
        
        foreach ($sql as $query) {
            if (Db::getInstance()->execute($query) == false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Enregistrer les hooks
     */
    protected function registerHooks()
    {
        return $this->module->registerHook('header') &&
            $this->module->registerHook('backOfficeHeader')
        ;
    }

}
