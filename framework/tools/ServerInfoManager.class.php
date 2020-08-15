<?php


namespace framework\tools;

/**
 * Class Server 获取服务器信息类
 */
class ServerInfoManager
{
    // 操作系统类型和版本
    public $systemInfo;
    // 服务器主机
    public $host;
    // php版本
    public $phpVersion;
    // web服务器信息(apache/nginx)
    public $webServerInfo;
    // mysql版本
    public $mysqlVersion;

    private static $instance;
    private function __construct(){
        // 获取操作系统类型
        /**
         * 功能：返回当前PHP所运行的系统的信息
         * @param string $mode
         *       'a':  返回所有信息
         *       's':  操作系统的名称，如FreeBSD
         *       'n':  主机的名称,如cnscn.org
         *       'r':  版本名，如5.1.2-RELEASE
         *       'v':  操作系统的版本号
         *       'm': 核心类型，如i386
         * @return string
         */
        $this -> systemInfo = php_uname("s")." ".php_uname("r");
        $this -> host = $_SERVER['HTTP_HOST'];
        $this -> phpVersion = PHP_VERSION;
        $this -> webServerInfo = $_SERVER['SERVER_SOFTWARE'];
        $link = mysqli_connect($GLOBALS["config"]["host"],$GLOBALS["config"]["user"],$GLOBALS["config"]["pass"]);
        $this -> mysqlVersion = mysqli_get_server_info($link);
    }
    private  function __clone(){}

    public static function getSingleTon(){
        if (!self::$instance instanceof self){
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getWebServerInfo(){
        // 尝试获取apache版本信息
        try {
            $this->webServerInfo = apache_get_version();
        }catch (\Exception $e){
            // 不是apache 获取nginx信息

        }
    }

    /**
     * 获取服务器磁盘空间详情
     */
    public function getDiskSpaceInfo(){
        $this -> __get("movieDirPath");
        if (strlen($this -> movieDirPath) > 0){
            $system = $_SESSION["serverInfo"]["system"];
            $path = "";
            if ($this -> isWindowsSystem()){
                // windows系统
                $dirs = explode("/",$this -> movieDirPath);
                if (count($dirs) > 0){
                    $path = $dirs[0];
                }else {
                    $path = "C:";
                }
            }else {
                // 非windows系统
                $path = "/";
            }

            $totalSpace = $this -> formatBytes(disk_total_space($path));
            $freeSpace = $this -> formatBytes(disk_free_space($path));
        }else {
            $totalSpace = $freeSpace = "0KB";
        }

        return [$totalSpace,$freeSpace,$path];
    }

    /**
     * 判断当前系统是不是windows
     * @return false|int
     */
    public function isWindowsSystem(){
        return strpos($this -> systemInfo,"Windows") !== false;
    }

    /**
     * 获取本机ip
     * @return string ip
     */
    public static function getLocalIp(){
        $preg = "/\A((([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\.){3}(([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\Z/";
        //获取操作系统为win2000/xp、win7的本机IP真实地址
        exec("ipconfig", $out, $stats);
        if (!empty($out)) {
            foreach ($out AS $row) {
                if (strstr($row, "IP") && strstr($row, ":") && !strstr($row, "IPv6")) {
                    $tmpIp = explode(":", $row);
                    if (preg_match($preg, trim($tmpIp[1]))) {
                        return trim($tmpIp[1]);
                    }
                }
            }
        }
        //获取操作系统为linux类型的本机IP真实地址
        exec("ifconfig", $out, $stats);
        if (!empty($out)) {
            if (isset($out[1]) && strstr($out[1], 'addr:')) {
                $tmpArray = explode(":", $out[1]);
                $tmpIp = explode(" ", $tmpArray[1]);
                if (preg_match($preg, trim($tmpIp[0]))) {
                    return trim($tmpIp[0]);
                }
            }
        }
        return '127.0.0.1';
    }

}