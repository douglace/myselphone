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

namespace Viaziza\Myselphone\Classes;

use Viaziza\Myselphone\Repository;
use ObjectModel;
use Product;
use Context;
use DbQuery;
use Cart;
use Db;
use Manufacturer;

class Model
{
    static $table = 'vz_model';

    /**
     * Retourne toutes l'équipes
     * @param int|null $id_lang
     * @param int|null $id_manufacturer
     * @param int|null $limit
     * @param int|null $offset
     * @return []|boolean
     */
    public static function getModels($id_lang = null, $id_manufacturer = null, $limit=null, $offset = 0) {
        $id_lang = $id_lang ? $id_lang : Context::getContext()->language->id;
        
        $q = new DbQuery();
        $q->select('a.*, b.name, b.description')
        ->from(self::$table, 'a')
        ->innerJoin(self::$table.'_lang', 'b', 'b.id_vz_model=a.id_vz_model')
        ->where('b.id_lang='.$id_lang);

        if($id_manufacturer) {
            $q->where('a.id_manufacturer='.$id_manufacturer);
        }
        if($limit) {
            $q->limit((int)$limit, (int)$offset);
        }

        $models = Db::getInstance()->executeS($q);

        if($models && !empty($models)) {
            return array_map(function($a)use($id_lang){
                $a['images'] = Image::getModelImages($a['id_vz_model']);
                $a['colors'] = Color::getColors($a['id_vz_model'], $id_lang);
                $a['storages'] = Storage::getStorages($a['id_vz_model'], $id_lang);
                $a['manufacturer'] = new Manufacturer($a['id_manufacturer']);
                return $a;
            }, $models);
        }

        return false;
    }

    /**
     * Permet de lier un model à des couleurs
     * @param int $id_model
     * @param int[] $colors
     */
    public static function attachColors($id_model, $colors) {
        Db::getInstance()->delete('vz_model_color', 'id_vz_model='.$id_model);
        if(empty($colors)) {
            return true;
        }
        $data = array_map(function($a)use($id_model){
            return [
                'id_vz_model' => $id_model,
                'id_vz_color' => $a,
            ];
        }, $colors);
        Db::getInstance()->insert('vz_model_color', $data, false, true, Db::INSERT_IGNORE);
    }

    /**
     * Permet de lier un model à des capacités de stockage
     * @param int $id_model
     * @param int[] $storages
     */
    public static function attachStorages($id_model, $storages) {
        Db::getInstance()->delete('vz_model_storage', 'id_vz_model='.$id_model);
        if(empty($storages)) {
            return true;
        }
        $data = array_map(function($a)use($id_model){
            return [
                'id_vz_model' => $id_model,
                'id_vz_storage' => $a,
            ];
        }, $storages);
        Db::getInstance()->insert('vz_model_storage', $data, false, true, Db::INSERT_IGNORE);
    }

    /**
     * Retourne les couleurs d'un model
     * @param int $id_model
     * @param int|null $id_lang
     * @return []|null|boolean
     */
    public static function getColors($id_model, $id_lang = null) {
        $id_lang = $id_lang ? $id_lang : Context::getContext()->language->id;
        
        $q = new DbQuery();
        $q->select('a.*, b.*')
        ->from('vz_color', 'a')
        ->innerJoin('vz_color_lang', 'b', 'b.id_vz_color=a.id_vz_color')
        ->innerJoin('vz_model_color', 'mc', 'mc.id_vz_color=a.id_vz_color')
        ->where('b.id_lang='.$id_lang)
        ->where('mc.id_vz_model='.$id_model);

        return Db::getInstance()->executeS($q);
    }

    /**
     * Retourne les capacités de stockage d'un model
     * @param int $id_model
     * @param int|null $id_lang
     * @return []|null|boolean
     */
    public static function getStorages($id_model, $id_lang = null) {
        $id_lang = $id_lang ? $id_lang : Context::getContext()->language->id;
        
        $q = new DbQuery();
        $q->select('a.*, b.*')
        ->from('vz_storage', 'a')
        ->innerJoin('vz_storage_lang', 'b', 'b.id_vz_storage=a.id_vz_storage')
        ->innerJoin('vz_model_storage', 'mc', 'mc.id_vz_storage=a.id_vz_storage')
        ->where('b.id_lang='.$id_lang)
        ->where('mc.id_vz_model='.$id_model);
        
        return Db::getInstance()->executeS($q);
    }

}