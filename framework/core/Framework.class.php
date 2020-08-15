<?php

namespace framework\core;

class Framework
{
    public function __construct($m='',$c='',$a='')
    {
        //初始化自动加载
        $this -> initAutoload();

        //初始化PATH_INFO
        $this -> initPathInfo();

        //初始化MCA
        $this -> initMCA($m,$c,$a);

        //分发请求
        $this -> initDispatch();
    }

    // 初始化自动加载
    private function initAutoload()
    {
        spl_autoload_register(array($this,'userAutoload'));
    }

    // 配置自动加载
    public function userAutoload($class)
    {
        if($class == 'Smarty'){
            require_once './framework/vendor/smarty/Smarty.class.php';
            return;
        }
        //1. 拆分字符串成数组
        $arr = explode('\\',$class);
        if($arr[0] == 'framework'){
            $basic_path = './';
        }else{
            $basic_path = './';
        }
        //2. 让需要的类（包含命名空间的）home\controller\GoodsController
        $sub_path = str_replace('\\','/',$class);

        //3. 确定后缀，类后缀是.class.php，接口后缀：.interface.php
        if(substr($arr[count($arr)-1],0,2) == 'i_'){
            $extension = '.interface.php';
        }else{
            $extension = '.class.php';
        }
        //4. 文件名
        $class_file = $basic_path.$sub_path.$extension;

        if(file_exists($class_file)){
            require_once $class_file;
        }
    }

    //直接从index.php拷贝过来的
    private function initMCA($modoule,$controller,$action)
    {
        //访问前台？后台？
        if (strlen($modoule) > 0){
            $m = $modoule;
        }else {
            $m = isset($_GET['m']) ? $_GET['m'] : $GLOBALS['config']['default_module'];
        }
        define('MODULE',$m);

        //访问哪个控制器
        if (strlen($controller) > 0){
            $c = $controller;
        }else {
            $c = isset($_GET['c']) ? $_GET['c'] : $GLOBALS['config']['default_controller'];
        }
        define('CONTROLLER',$c);

        //访问哪个方法
        if (strlen($action) > 0){
            $a = $action;
        }else {
            $a = isset($_GET['a']) ? $_GET['a'] : $GLOBALS['config']['default_action'];
        }
        define('ACTION',$a);
    }

    // 控制器派发
    private function initDispatch()
    {
        // 获取类名前缀
        $preString = substr(CONTROLLER,0,3);

        //实例化控制器对象
        if (strlen(CONTROLLER) > 0){
            if ($preString == "API"){
                $posControllerNameArr = [
                    MODULE.'\\controller\\'.'API\\'.CONTROLLER.'Controller',
                    MODULE.'\\controller\\'.'API\\'.'Local\\'.CONTROLLER.'Controller',
                    MODULE.'\\controller\\'.'API\\'.'Online\\'.CONTROLLER.'Controller',
                ];

                foreach ($posControllerNameArr as $value){
                    if (class_exists($value)){
                        $controllerName = $value;
                        break;
                    }
                }
            }else {
                $controllerName = MODULE.'\\controller\\'.CONTROLLER.'Controller';
            }

        }else {
            $controllerName = 'framework\\'.'core\\'.CONTROLLER.'Controller';
        }

        $controller = new $controllerName;

        //调用控制器的方法
        $a = ACTION;
        $controller -> $a();
    }

    //初始化index.php后面的参数，PATHINFO
    private function initPathInfo()
    {
        if(isset($_SERVER['PATH_INFO']) && $info = $_SERVER['PATH_INFO']){
            //再处理: /home/index/index.html
            //1. 先将.html替换掉
            $extension = strrchr($info,'.');
            $path = str_replace($extension,'',$info);

            //2. 再将左边的 / 去掉： /home/index/index
            $path = substr($path,1); //   home/index/index

            //3. 炸开，炸成一个数组
            $arr = explode('/',$path);
            $count = count($arr);
            if($count == 3){
                $_REQUEST['m'] = $arr[0];
                $_REQUEST['c'] = $arr[1];
                $_REQUEST['a'] = $arr[2];
            }else if($count==2){
                $_REQUEST['m'] = $arr[0];
                $_REQUEST['c'] = $arr[1];
            }else if($count==1){
                $_REQUEST['m'] = $arr[0];
            }else{
                //大于3个情况：/home/index/index/page/5
                //从第三个参数之后，每2个是一对参数：
                $_REQUEST['m'] = $arr[0];
                $_REQUEST['c'] = $arr[1];
                $_REQUEST['a'] = $arr[2];

                for($i=3;$i<$count;$i+=2){
                    $_REQUEST[$arr[$i]] = $arr[$i+1];
                }
            }
        }

    }
}