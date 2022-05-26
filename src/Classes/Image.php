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

class Image
{
    static $table = 'vz_image';

    public static function getImgPath($front = false){
        return $front ? Repository::$img_model_img_front : Repository::$img_model_img_dir;
    }

    /**
     * Retourne toutes les images d'un model
     * @param int|null $id_model
     * @return []|boolean
     */
    public static function getImages($id_model = null) {
        $q = new DbQuery();
        $q->select('*')
        ->from(self::$table)
        ->orderBy('position ASC');

        if($id_model) {
            $q->where('id_vz_model='.$id_model);
        }

        return array_map(function($a){
            $a['link'] = self::getImgPath(true).'/'.$a['id_vz_image'].'.jpg';
            return $a;
        }, Db::getInstance()->executeS($q));
    }

    /**
     * Retourne toutes les images d'un model
     * @param int|null $id_model
     * @return []|boolean
     */
    public static function getModelImages($id_model) {
        return self::getImages($id_model);
    }
}