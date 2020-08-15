<?php
function smarty_insert_getPrice($param)
{
    //echo '<pre>';
    //var_dump($param);
    //获得商品的id
    $goods_id = $param['id'];

    $sql = "SELECT shop_price FROM tn_goods WHERE goods_id = $goods_id";
    //函数里面使用全局空间的$dao变量呢
    require_once 'dao/DAOPDO.class.php';
    $option = [
        'host'      =>  'localhost',
        'user'      =>  'root',
        'pass'      =>  'root',
        'dbname'    =>  'php_10',
        'port'      =>  3306,
        'charset'   =>  'utf8'
    ];
    $dao = DAOPDO::getSingleton($option);
    return $dao -> fetchColumn($sql);
}