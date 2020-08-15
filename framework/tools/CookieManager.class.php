<?php

class CookieManager
{
    //写cookies
    public static function setCookie($name,$value){
        // 30天过期
         $days = 30;
         $exp = time() + $days*24*60*60*1000;
         setcookie($name,$value,$exp);
    }

    //读取cookies
    public static function getCookie($name){
        if (isset($_COOKIE[$name])){
            return $_COOKIE[$name];
        }else {
            return "";
        }
    }

    //删除cookies
    public static function delCookie($name){
        // 设置cookie过期时间为过去一小时触发浏览器删除cookie机制
//        setcookie($name,time()-3600);
        // 另外一种方式
        setcookie($name,"");
    }
}