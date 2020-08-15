<?php

namespace framework\tools;

/**
 * 利用curl发送http请求类
 */
class HttpRequest
{
    //请求的服务器地址
    private $url;
    //直接输出结果还是将结果返回
    private $is_return = 1;
    // 当前的下载进度
    private $downloadProgress;

    public function __set($name, $value)
    {
        if(property_exists($this,$name)){
            $this -> $name = $value;
        }
    }
    public function __get($name)
    {
        if(property_exists($this,$name)){
            return $this -> $name;
        }
    }
    //封装发送http请求的方法
    //参数：请求第三方服务器时携带的数据
    //如果携带的数据为空采用get方式请求，如果携带的数据不为空，则采用post方式提交
    public function send($data=[])
    {
        //1. 初始化curl请求
        $ch = curl_init();
        //2. 设置请求的服务器地址
        curl_setopt($ch,CURLOPT_URL,$this->url);
        //3. 不管get、post，都跳过证书验证
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        // ps: 如果目标网页跳转，也跟着跳转
        // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        //4. 判断是get请求还是post请求
        if($data && is_array($data)){
            //说明post请求
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        }

        //判断是直接将结果显示echo还是return
        //设置结果返回
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        $result = curl_exec($ch);
        // 处理重定向的请求
        $redirectUrl = curl_getinfo($ch)["redirect_url"];
        if ($redirectUrl){
            // 请求被重定向了 继续请求重定向
            $this -> url = $redirectUrl;
            $result = $this -> send($data);
        }

        //4. 关闭资源
        curl_close($ch);
        return $result;
    }

    /**
     * 处理请求重定向问题
     * @param $ch
     * @param $data
     * @return bool|string
     */
    private function redirectHandle($ch,$data){
        // 处理重定向的请求
        $redirectUrl = curl_getinfo($ch)["redirect_url"];
        if ($redirectUrl){
            // 请求被重定向了 继续请求重定向
            $this -> url = $redirectUrl;
            $result = $this -> send($data);
            return $result;
        }

        return "";
    }

    /**
     * 判断网址是否可达
     * @return bool
     */
    public static function reachable($url){
        $ch = curl_init ();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_NOBODY, FALSE);
        #curl_setopt( $ch, CURLOPT_POSTFIELDS, "username=".$username."&password=".$password );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_exec($ch);
        $returnCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        return  $returnCode;
    }

    /**
     * 判断远程文件是否存在
     * @return bool
     */
    public function chk_remote_file_exists() {
        $ch = curl_init();
        $timeout = 10;
        curl_setopt ($ch, CURLOPT_URL, $this -> url);
        curl_setopt ($ch, CURLOPT_HEADER, 1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $contents = curl_exec($ch);
        if (preg_match("/404/", $contents)) {
            return false;
        }
        return true;
    }

    /**
     * 下载文件
     * @param $savePath             文件保存路径
     * @param $progressCallBack     下载进度更新回调
     */
    public function download($savePath,$proCallBack){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this -> url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($ch, $countDownloadSize, $currentDownloadSize, $countUploadSize, $currentUploadSize) use($proCallBack){
            // 计算下载进度
            if ($countDownloadSize > 0){
                $pro = $currentDownloadSize / $countDownloadSize * 100;
                // 保留两位小数
                $pro = number_format($pro, 2);
                // 防止在进度变化不大的情况下过于频繁的调用回调
                if ($pro - $this->downloadProgress >= 1){
                    $this->downloadProgress = $pro;
                    $proCallBack($pro);
                }
            }
        });
        curl_setopt($ch, CURLOPT_NOPROGRESS, false); // needed to make progress function work
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        $stream = curl_exec($ch);

        // 保存文件到指定路径
        $f=fopen($savePath,'w');
        fwrite($f,$stream);
        curl_close($ch);
    }

    /**
     * 下载进度更新回调
     * @param $ch
     * @param $countDownloadSize
     * @param $currentDownloadSize
     * @param $countUploadSize
     * @param $currentUploadSize
     */
    function progress($ch, $countDownloadSize, $currentDownloadSize, $countUploadSize, $currentUploadSize)
    {
        // 开始计算
        $pro = 0;
        if ($countDownloadSize > 0){
            $pro = $currentDownloadSize / $countDownloadSize * 100;
            $this->downloadProgressCallBack($pro);
        }
    }

    // 获取https文件内容
    public static function  getHttpsContent($url){
//        if (HttpRequest::reachable($url) == 0){
//            $stream_opts = [
//                "ssl" => [
//                    "verify_peer"=>false,
//                    "verify_peer_name"=>false,
//                ]
//            ];
//            $content = file_get_contents($url,false, stream_context_create($stream_opts));
//        }else {
//            $content = file_get_contents($url);
//        }
        $stream_opts = [
            "ssl" => [
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ]
        ];

        $content = file_get_contents($url,false, stream_context_create($stream_opts));

        return $content;
    }
}