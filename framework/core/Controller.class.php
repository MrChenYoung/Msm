<?php
namespace framework\core;
use admin\controller\API\Online\API_GetOnlineMoviesController;
use framework\tools\LogManager;
use framework\tools\Page;
use framework\tools\ServerInfoManager;
use framework\tools\SessionManager;
use \Smarty;

class Controller
{
    // 模板
    protected $smarty;

    // 数据
    protected $data;
    // 数据额外信息  子类控制器自己定义的数据信息
    protected $dataExtension;

    // 本站站点路径
    protected $website;

    public function __construct()
    {
        $this -> smarty = new Smarty();
        $this -> smarty -> left_delimiter = '<{';
        $this -> smarty -> right_delimiter = '}>';
        $tplsDir = ROOT.'public/tpls_c';
        if (!file_exists($tplsDir)){
            mkdir($tplsDir);
            chmod($tplsDir,0700);
        }
        $this -> smarty -> setTemplateDir(ROOT.MODULE.'/view/');
        $this -> smarty -> setCompileDir($tplsDir);

        // 本站站点
        $this -> website = "http://".$_SERVER['HTTP_HOST'];
        // 图片路径
        $imageUrl = $this->website."/public/common/img/";

        // 数据
        $this -> data = [
            "baseUrl"       => $this -> website,
            "imageUrl"      => $imageUrl,
            "dbname"        => $GLOBALS["db_info"]["dbname"]
        ];

        $spaceInfo = $this -> getMovieDiskSpace();
        $this -> dataExtension = ["totalSpace" => $spaceInfo[0],"freeSpace" => $spaceInfo[1]];
    }

    /**
     * 每个控制器的入口函数
     */
    public function index(){}

    /**
     * 获取在线视频数据
     * @return array
     */
    private function getOnlineMovieData(){
        $APIOnlineCtr = new API_GetOnlineMoviesController();
        $res = $APIOnlineCtr -> getOnlineMovieSourceList(true);

        $data = [];
        foreach ($res as $ctr=>$re) {
            $domId = "online_".$re["sourceId"];
            $controller = $ctr;
            $searchSupport = $re["search"] ? "1" : "0";
            $href = "?m=admin&c=OnlineMovie&a=index&domId=".$domId.
                "&search=".$searchSupport.
                "&movieController=".$controller.
                "&rowCount=".$re["rowCount"].
                "&columnCount=".$re["columnCount"];
            $sourceName = $re["sourceName"];

            $dom = <<<EEE
                    <li>
                        <a id=$domId href=$href>
                            <span>$sourceName</span>
                        </a>
                    </li>
EEE;
            $data[] = $dom;
        }

        return $data;
    }

    /**
     *  字节格式化成B KB MB GB TB
     * @param $size 字节大小
     * @return string 格式化后的结果
     */
    public function formatBytes($size) {
        $scale = ServerInfoManager::getSingleTon() -> isWindowsSystem() ? 1024 : 1000;
        $units = array(' B', ' KB', ' MB', ' GB', ' TB');
        for ($i = 0; $size >= $scale && $i < 4; $i++) {
            $size /= $scale;
        }
        return round($size, 2).$units[$i];
    }

    /**
     * 加载模板
     */
    protected function loadTemplate($data,$temPath){
        // 额外数据信息追加
        if ($this -> dataExtension){
            foreach ($this -> dataExtension as $eKey => $eValue) {
                $data["data"][$eKey] = $eValue;
            }
        }

        // 设置在线视频信息
        $onlineData = $this -> getOnlineMovieData();
        $data["data"]["onlineData"] = $onlineData;

        $state = $this -> getSlideBarState();
        $data["data"]["slideBarState"] = $state;

        // 设置模板
        foreach ($data as $key => $datum) {
            $this -> smarty -> assign($key,$datum);
        }
        $this -> smarty -> display($temPath);
    }

    //跳转、重定向
    //参数1：url，目标地址
    //参数2：message, 提示的信息
    //参数3：delay，延迟的时间，默认3秒
    public function jump($url,$message,$delay=3)
    {
        header("Refresh:$delay;url=$url");
        echo $message;
        exit;
    }

    /**
     * 存储菜单栏显示装填
     */
    public function saveSlideBarState(){
        $stateHidden = $_REQUEST["slidebarState"];
        SessionManager::getSingleTon() -> setSession("slidebarState",$stateHidden);
    }

    /**
     * 获取菜单栏隐藏状态
     * @return mixed
     */
    private function getSlideBarState(){
        $state = SessionManager::getSingleTon() -> getSession("slidebarState");
        if (strlen($state) <= 0){
            // 默认显示菜单栏
            $state = "0";
        }
        return $state;
    }

    /**
     * 获取服务器磁盘空间使用详情
     */
    public function getMovieDiskSpace(){
        $totalSpace = $freeSpace = "0KB";
        $path = "/";

        $totalSpace = $this -> formatBytes(disk_total_space($path));
        $freeSpace = $this -> formatBytes(disk_free_space($path));

        return [$totalSpace,$freeSpace];
    }
}

