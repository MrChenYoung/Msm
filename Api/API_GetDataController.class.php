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
}