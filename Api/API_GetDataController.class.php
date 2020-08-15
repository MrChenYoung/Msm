<?php


namespace API;

use framework\tools\DatabaseDataManager;
require_once './Api/API_BaseController.class.php';
require_once './framework/tools/DatabaseDataManager.class.php';

class API_GetDataController extends API_BaseController
{
    // 获取所有的手机号
    public function loadPhones(){
        $res = DatabaseDataManager::getSingleton()->find("msm_phone");
        if ($res){
            return $this->success($res);
        }else {
            return $this->failed("查询手机号码失败");
        }
    }

    // 存储位置信息
    public function saveAddress(){
        $lng = $_REQUEST["lng"];
        $lat = $_REQUEST["lat"];
        $address = $_REQUEST["add"];
        $phoneInfo = $_REQUEST["phone"];

        // 获取当前时间
        date_default_timezone_set('PRC');
        $time = date('Y-m-d H:i:s', time());

        $data = [
            "time"          =>  $time,
            "phone_info"    =>  $phoneInfo,
            "longitude"     =>  $lng,
            "latitude"      =>  $lat,
            "address"       =>  $address
        ];
        $lastId = DatabaseDataManager::getSingleton()->insert("msm_location",$data);
        if ($lastId){
            echo $this->success("上传成功");
        }else {
            echo $this->failed("上传失败");
        }
    }

    // 获取位置信息
    public function loadAddress(){
        $res = DatabaseDataManager::getSingleton()->find("msm_location");
        if ($res){
            return $this->success($res);
        }else {
            return $this->failed("获取位置信息失败");
        }
    }
}