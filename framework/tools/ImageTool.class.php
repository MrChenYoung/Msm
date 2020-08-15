<?php


namespace framework\tools;


class ImageTool
{
    // 图片base64编码
    /**
     * 图片base64编码
     * @param string $img
     * @param bool $imgHtmlCode
     * author 江南极客
     * @return string
     */
    public static function imgBase64Encode($url = '', $imgHtmlCode = true)
    {
        //如果是本地文件
        if(strpos($url,'http') === false && !file_exists($url)){
            return $url;
        }

        //获取文件内容
        //1. 初始化curl请求
        $ch = curl_init();
        //2. 设置请求的服务器地址
        curl_setopt($ch,CURLOPT_URL,$url);
        //3. 不管get、post，都跳过证书验证
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        //设置结果返回
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        $file_content = curl_exec($ch);
        $returnCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        if($returnCode != 200 || $file_content === false){
            return false;
        }
        $imageInfo = getimagesize($url);
        $prefiex = '';
        if($imgHtmlCode){
            $prefiex = 'data:' . $imageInfo['mime'] . ';base64,';
        }
        $base64 = $prefiex.chunk_split(base64_encode($file_content));
        return $base64;
    }

    // 原始图片内容转换成base64
    public static function imageContentToBase64($imageContent){
        if (strlen($imageContent) == 0) die("图片内容为空");

        $base64 = chunk_split(base64_encode($imageContent));
        $base64 = 'data:image/jpg/png/gif;base64,'.$base64;
        return $base64;
    }

    /**
     * 保存base64格式的图片为本地文件
     * @param $base64_image_content     base64格式图片
     * @param $path                     图片保存的文件夹
     */
    public static function saveBase64ToImage($base64_image_content,$path){
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
            $type = $result[2];
            if (!file_exists($path)){
                mkdir($path);
                chmod($path,0777);
            }
            $fileName = time() . ".{$type}";
            $filePath = $path ."/".$fileName;
            if (file_put_contents($filePath, base64_decode(str_replace($result[1], '', $base64_image_content)))){
                // 保存成功
                return $fileName;
            } else {
                return false;
            }
        }
    }
}