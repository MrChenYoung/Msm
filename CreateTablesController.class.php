<?php

use framework\dao;

require_once "./framework/dao/DAOPDO.class.php";
class CreateTablesController
{
    private $dao;
    public function __construct()
    {
        $this->initDabaseInfo();
        $this->dao = dao\DAOPDO::getSingleton();

        // 初始化手机号码表
        $this->initPhoneNumTable();
        // 初始化位置表
        $this -> initLocationTable();
    }

    // 初始化数据库信息
    private function initDabaseInfo(){
        // 获取数据库链接信息
        $path = getcwd()."/config/config.php";
        $config = require_once $path;
        $GLOBALS['config'] = $config;

        $option['host'] = $config['host'];
        $option['user'] = $config['user'];
        $option['pass'] = $config['pass'];
        $option['dbname'] = $config['dbname'];
        $option['port'] = $config['port'];
        $option['charset'] = $config["charset"];
        $GLOBALS["db_info"] = $option;
    }

    // 创建手机号码表
    public function initPhoneNumTable(){
        $tableName = "msm_phone";
        // 创建手机号码表
        $sql = <<<EEE
                    CREATE TABLE $tableName(
                        id int AUTO_INCREMENT PRIMARY KEY COMMENT '编号',
                        phone_number varchar(64) DEFAULT '' COMMENT '手机号码'
                    ) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='手机号码表';
EEE;
        $this->dao->createTable($tableName,$sql);
        // 添加手机号码
        $data = [[
            "phone_number"     =>  "'13884406960'"
        ],[
            "phone_number"     =>  "'18829221512'"
        ],[
            "phone_number"     =>  "'13761943167'"
        ],[
            "phone_number"     =>  "'13213699645'"
        ]];

        foreach ($data as $datum) {
            $this->dao->insertData($tableName, "phone_number", $datum);
        }
    }

    /**
     * 初始化位置表
     */
    public function initLocationTable(){
        $tableName = "msm_location";

        // 创建位置表
        $sql = <<<EEE
                    CREATE TABLE $tableName(
                        id int AUTO_INCREMENT PRIMARY KEY COMMENT '编号',
                        time varchar(128) DEFAULT '' COMMENT '时间',
                        phone_info varchar(128) DEFAULT '' COMMENT '手机信息',
                        longitude varchar(128) DEFAULT '' COMMENT '经度',
                        latitude varchar(128) DEFAULT '' COMMENT '纬度',
                        address varchar(128) DEFAULT '' COMMENT '地址'
                    ) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci COMMENT='位置表';
EEE;
        $this->dao->createTable($tableName,$sql);
    }
}