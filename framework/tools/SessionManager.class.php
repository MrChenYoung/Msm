<?php


namespace framework\tools;

/**
 * Class SessionManager Session管理类
 */
class SessionManager
{
    private static $instance;
    private function __construct(){
        session_start();
    }
    private  function __clone(){}

    public static function getSingleTon(){
        if (!self::$instance instanceof self){
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * 添加session键值对
     * @param $key
     * @param $value
     */
    public function setSession($key,$value){
        $_SESSION[$key] = $value;
    }

    /**
     * 获取指定key的session值
     * @param $key
     * @return mixed
     */
    public function getSession($key){
        if (isset($_SESSION[$key])){
            return $_SESSION[$key];
        }else {
            return "";
        }
    }

    /**
     * 删除session中指定key
     * @param $key
     */
    public function deleteSession($key){
        if (isset($_SESSION[$key])){
            unset($_SESSION[$key]);
        }
    }

    /**
     * 检查是否在session中设置了key
     * @param $key
     * @return bool
     */
    public function issetSession($key){
        return isset($_SESSION[$key]);
    }
}