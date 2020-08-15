<?php

namespace framework\dao;
use admin\controller\AutoExecController;

/**
 * Class DAOMySqli Mysqli封装类
 * 封装Mysqli操作
 */
class DAOMySqli implements i_DAOPDO
{
    private static $instance;
    private $host;
    private $user;
    private $pass;
    private $port;
    private $charset;
    private $dbname;
    private $mysqli;

    // 私有化构造方法
    public function __construct($option){
        $this -> host = isset($option['host'])?$option['host']:'';
        $this -> user = isset($option['user'])?$option['user']:'';
        $this -> pass = isset($option['pass'])?$option['pass']:'';
        $this -> dbname = isset($option['dbname'])?$option['dbname']:'';
        $this -> port = isset($option['port'])?$option['port']:3306;
        $this -> charset = isset($option['charset'])?$option['charset']:'utf8';


        // 初始化mysqli
        $this -> initMysqli();
    }

    // 初始化mysqli
    private function initMysqli(){
        $this -> mysqli = new \mysqli($this -> host,$this -> user,$this -> pass,$this -> dbname,$this -> port);

        if ($this -> mysqli -> connect_error){
            echo "数据库连接失败：".$this -> mysqli -> connect_error;
        }
    }

    // 私有化__clone魔术方法
    private function __clone(){}

    // 获取单利静态方法
    public static function getSingleton($option)
    {
        if(!self::$instance instanceof self){
            self::$instance = new self($option);
        }
        return self::$instance;
    }

    /**
     * 数据库链接是否成功
     * @return bool
     */
    public function checkConnectSuccess(){
        if ($this -> mysqli -> connect_error){
            return false;
        }else {
            return true;
        }
    }

    //查询一条记录的方法
    public function fetchOne($sql){
        $result = $this -> mysqli -> query($sql);

        if ($result){
            // 查询成功 获取一行结果 只获取关联数组
            $row = $result -> fetch_array(MYSQLI_ASSOC);
            $result -> free();
            return $row;
        }else {
            // 查询失败
            echo "查询失败,错误如下:".$this -> mysqli -> error;
            $result -> free();
            exit;
        }
    }

    //查询所有记录
    public function fetchAll($sql){
        $result = $this -> mysqli -> query($sql);

        if ($result){
            // 获取所有行数据 只要关联数组
            $res = $result -> fetch_all(MYSQLI_ASSOC);
            // 释放资源
            $result -> free();
            return $res;
        }else {
            // 查询失败
            echo "查询失败,错误如下:".$this -> mysqli -> error;
            $result -> free();
            exit;
        }
    }

    //查询一个字段的值
    public function fetchColumn($sql){
        $result = $this -> mysqli -> query($sql);

        if ($result){
            // 获取所有行数据 只要关联数组
            $res = $result -> fetch_field();
            return $res;
        }else {
            // 查询失败
            echo "查询失败,错误如下:".$this -> mysqli -> error;
            exit;
        }
    }

    //执行增删改的方法
    public function query($sql){
        $result = $this -> mysqli -> query($sql);
        if ($result){
            return $result;
        }else {
            echo "SQL语句有误:".$this -> mysqli -> error;
            exit;
        }
    }

    //返回刚刚执行插入语句的主键
    public function lastId(){
        return $this -> mysqli -> insert_id;
    }

    //对数据引号转义、并包裹的方法
    public function quote($data){

    }

    /**
     * 获取编码方式
     * @return mixed
     */
    public function getCharset(){
        return $this -> mysqli -> get_charset();
    }

    /**
     * 设置编码方式
     * @param $charset 编码方式
     */
    public function setCharset($charset){
        $this -> mysqli -> set_charset($charset);
    }
}