<?php


namespace framework\tools;
use framework\dao\DAOPDO;

class DatabaseManager
{
    protected $dao;
    private static $instance;
    private function __construct(){}
    private function __clone(){}
    public static function getSingleton(){
        $calledClass = get_called_class();
        if(!self::$instance instanceof $calledClass){
            self::$instance = new $calledClass();
            self::$instance->dao = DAOPDO::getSingleton();
        }
        return self::$instance;
    }

    // 执行sql语句
    public function fetch($sql){
        return $this->dao->fetchAll($sql);
    }

    //执行增删改的方法
    public function query($sql)
    {
        return $this->dao->query($sql);
    }
}