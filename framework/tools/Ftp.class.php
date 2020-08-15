<?php

namespace framework\tools;

/**
 * Class Ftp Ftp操作类
 */
class Ftp
{
    public $off;             // 返回操作状态(成功/失败)
    public $conn_id;           // FTP连接
    public $login_id;           // 登录FTP id

    /**
     * 方法：FTP连接
     * @FTP_HOST -- FTP主机
     * @FTP_PORT -- 端口
     * @FTP_USER -- 用户名
     * @FTP_PASS -- 密码
     */
    public function __construct($FTP_HOST, $FTP_USER, $FTP_PASS="", $FTP_PORT="")
    {
        $this->conn_id = @ftp_connect($FTP_HOST, $FTP_PORT);
        if ($this -> conn_id){
            // 链接ftp服务器成功 登录
            $this -> login_id = @ftp_login($this->conn_id, $FTP_USER, $FTP_PASS);
            if ($this -> login_id){
                // 登录成功
                @ftp_pasv($this->conn_id, 1); // 打开被动模拟
            }
        }
    }

    /**
     * 登录frp是否成功 登录成功才可以进行后续文件操作
     * @return bool
     */
    public function loginSuccess(){
        if ($this -> conn_id && $this -> login_id){
            // 已经登录成功
            return true;
        }else {
            return false;
        }
    }

    /**
     * 方法：上传文件
     * @path  -- 本地路径
     * @newpath -- 上传路径
     * @type  -- 若目标目录不存在则新建
     */
    public function up_file($path, $newpath, $type = true)
    {
        if (!$this -> loginSuccess()) {
            echo "FTP服务器链接失败";
            return false;
        }
        if ($type) $this->dir_mkdirs($newpath);
        $this->off = @ftp_put($this->conn_id, $newpath, $path, FTP_BINARY);
        if (!$this->off){
            // 上传失败
            echo "文件上传失败,请检查权限及路径是否正确！";
            return false;
        }

        return true;
    }

    /**
     * 上传文件夹
     * @param $localPath 本地文件夹路径
     * @param $remotePath 远程文件夹路径
     * @return bool
     */
    public function up_dir($localPath,$remotePath){
        if (!$this -> loginSuccess()) {
            echo "FTP服务器链接失败";
            return false;
        }

        if (!file_exists($localPath)){
            echo "本地文件夹不存在";
            return false;
        }

        // 如果是文件 直接上传
        if (is_file($localPath)){
            $result = $this -> up_file($localPath,$remotePath);
            return $result;
        }

        // 如果是文件夹 遍历并上传
        $res = true;
        $handler = opendir($localPath);
        while (($filename = readdir($handler)) !== false) {
            if ($filename != "." && $filename != "..") {
                $localFullPath = $localPath . "/$filename";
                $remoteFullPath = $remotePath . "/$filename";
                // 递归上传
                $res = $this->up_dir($localFullPath, $remoteFullPath);

                if ($res == false){
                    // 失败
                    break;
                }
            }
        }

        return $res;
    }

    /**
     * 方法：移动文件
     * @path  -- 原路径
     * @newpath -- 新路径
     * @type  -- 若目标目录不存在则新建
     */
    public function move_file($path, $newpath, $type = true)
    {
        if (!$this -> loginSuccess()) {
            echo "FTP服务器链接失败";
            return false;
        }

        if ($type) $this->dir_mkdirs($newpath);
        $this->off = @ftp_rename($this->conn_id, $path, $newpath);
        if (!$this->off) echo "文件移动失败,请检查权限及原路径是否正确！";
    }

    /**
     * 方法：复制文件
     * 说明：由于FTP无复制命令,本方法变通操作为：下载后再上传到新的路径
     * @path  -- 原路径
     * @newpath -- 新路径
     * @type  -- 若目标目录不存在则新建
     */
    public function copy_file($path, $newpath, $type = true)
    {
        if (!$this -> loginSuccess()) {
            echo "FTP服务器链接失败";
            return false;
        }

        $downpath = "c:/tmp.dat";
        $this->off = @ftp_get($this->conn_id, $downpath, $path, FTP_BINARY);// 下载
        if (!$this->off) echo "文件复制失败,请检查权限及原路径是否正确！";
        $this->up_file($downpath, $newpath, $type);
    }

    /**
     * 方法：删除文件
     * @param $path 路径
     * @return bool 是否删除成功
     */
    public function del_file($path)
    {
        if (!$this -> loginSuccess()) {
            echo "FTP服务器链接失败";
            return false;
        }

        $this->off = @ftp_delete($this->conn_id, $path);
        if (!$this->off){
            // 删除失败
            return false;
        }else {
            // 删除成功
            return true;
        }
    }

    /**
     * 删除文件夹(如果内部不为空先删除内部内容)
     * @param $path 文件夹路径
     */
    public function del_dir($path){
        if (!$this -> loginSuccess()) {
            echo "FTP服务器链接失败";
            return false;
        }

        $file_arr = ftp_nlist($this -> conn_id, $path);

        // 删除文件夹内部文件和文件夹
        foreach ($file_arr as $subPath) {
            if ($this -> isDir($subPath)){
                // 是文件夹 递归删除内部文件
                $result = $this -> del_dir($subPath);
                if (!$result){
                    return false;
                }
            }else {
                // 文件 直接删除
                $result = $this -> del_file($subPath);
                if (!$result){
                    return false;
                }
            }
        }

        // 删除文件夹
        $result = ftp_rmdir($this -> conn_id,$path);
        return $result;
    }

    /**
     * 判断文件是文件还是文件夹
     * @param $path 路径
     * @return bool 结果
     */
    public function isDir($path){
        if (!$this -> loginSuccess()) {
            echo "FTP服务器链接失败";
            return false;
        }

        if (ftp_size($this -> conn_id,$path) == -1){
            // 是文件夹
            return true;
        }else {
            // 是文件
            return false;
        }
    }

    /**
     * 方法：生成目录
     * @path -- 路径
     */
    function dir_mkdirs($path)
    {
        if (!$this -> loginSuccess()) {
            echo "FTP服务器链接失败";
            return false;
        }

        $path_arr = explode('/', $path);       // 取目录数组
        $file_name = array_pop($path_arr);      // 弹出文件名
        $path_div = count($path_arr);        // 取层数

        foreach ($path_arr as $val)          // 创建目录
        {
            if (@ftp_chdir($this->conn_id, $val) == FALSE) {
                $tmp = @ftp_mkdir($this->conn_id, $val);
                if ($tmp == FALSE) {
                    echo "目录创建失败,请检查权限及路径是否正确！";
                    exit;
                }
                @ftp_chdir($this->conn_id, $val);
            }
        }

        for ($i = 1; $i <= $path_div; $i++)         // 回退到根
        {
            @ftp_cdup($this->conn_id);
        }
    }

    /**
     * 方法：关闭FTP连接
     */
    function close()
    {
        @ftp_close($this->conn_id);
    }
}
