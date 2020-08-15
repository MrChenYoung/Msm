<?php

// 创建数据库
require_once './CreateTablesController.class.php';
new CreateTablesController();

// 请求指定接口
require_once './Api/API_GetDataController.class.php';
$ctr = new \API\API_GetDataController();
$action = $_REQUEST["a"];
echo $ctr->$action();