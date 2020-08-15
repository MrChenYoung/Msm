<?php


namespace framework\tools;

/**
 * Class FileManager 文件管理类
 */
class FileManager
{
    /**
     * 删除文件夹(递归删除,先删除内部所有文件和文件夹再删除自身)
     * @param $dirPath
     */
    public static function deleteDir($dirPath){
        $handler = file_exists($dirPath) ? opendir($dirPath) : null;
        if ($handler){
            while (($filename = readdir($handler)) !== false) {//务必使用!==，防止目录下出现类似文件名“0”等情况
                if ($filename != "." && $filename != "..") {
                    $movieFullPath = $dirPath."/$filename";
                    if (is_file($movieFullPath)){
                        // 如果是文件 删除
                        unlink($movieFullPath);
                    }else {
                        // 如果是文件夹 递归删除
                        self::deleteDir($movieFullPath);
                    }
                }
            }
            closedir($handler);
            rmdir($dirPath);
        }
    }

    /**
     * 获取一个文件夹下所有文件名 (包括文件和文件夹)
     * @param $dir
     * @return array
     */
    public static function getFilesInDir($dir){
        $handler = file_exists($dir) ? opendir($dir) : null;
        $files = [];
        if ($handler){
            while (($filename = readdir($handler)) !== false) {//务必使用!==，防止目录下出现类似文件名“0”等情况
                if ($filename != "." && $filename != "..") {
                    $files[] = $filename;
                }
            }
            closedir($handler);
        }

        return $files;
    }

    /**
     * 获取文件真正的mimetype(文件类型),修改后缀不能改变文件原本类型
     * @param $filePath 文件路径
     * @return mixed    文件真正的mimetype
     */
    public static function getMimeType($filePath){
        $handle=finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = finfo_file($handle,$filePath);
        finfo_close($handle);
        return $mimetype;
    }

    /**
     * 逐行读取文件
     * @param $txtfile 文件路径
     * @return array|string 结果
     */
    public static function fileContentOneByOne($filePath){
        $file = @fopen($filePath,'r');
        $content = array();
        if(!$file){
            return 'file open fail';
        }else{
            $i = 0;
            while (!feof($file)){
                $content[$i] = mb_convert_encoding(fgets($file),"UTF-8","GBK,ASCII,ANSI,UTF-8");
                $i++ ;
            }
            fclose($file);
            $content = array_filter($content); //数组去空
        }

        return $content;
    }

    /**
     *  字节格式化成B KB MB GB TB
     * @param $size 字节大小
     * @return string 格式化后的结果
     */
    public static function formatBytes($size) {
        $units = array(' B', ' KB', ' MB', ' GB', ' TB');
        for ($i = 0; $size >= 1024 && $i < 4; $i++) {
            $size /= 1024;
        }
        return round($size, 2).$units[$i];
    }

    /**
     * 获取文件的大小（不支持m3u8）
     * @param $path                文件路径(可以是在线的也可以是本地的)
     * @return false|int|mixed     获取文件的大小 单位bytes
     */
    public static function getFileSize($path){
        $httpPre = substr($path,0,strlen("http://"));
        $httpsPre = substr($path,0,strlen("https://"));

        $fileLength = 0;
        if ($httpPre == "http://" || $httpsPre == "https://"){
            // 远程的
            $res = get_headers($path,true);
            $fileLength = $res['Content-Length'];
        }else{
            // 本地的
            $fileLength = filesize($path);
        }

        return $fileLength;
    }
}