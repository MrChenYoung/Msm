<?php


namespace framework\tools;

/**
 * Class ImageEncrypt 图片加密处理类
 * @package framework\tools
 */
class ImageEncrypt
{
    /**
     * 通过Data URI协议和base64编码给图片加密
     * Data URI 的语法: data:[<mediatype>][;base64],<data>
     * HTML可以直接设置src属性为Data URI来显示图片
     * @param $filePath 图片路径
     * @param $savePath 加密后图片存储路径
     */
    static public function base64Encode($filePath,$savePath){
        if (!file_exists($filePath)){
            echo "文件不存在";
            return;
        }

        $file_content = file_get_contents($filePath);
        $imageInfo = getimagesize($filePath);
        $prefiex = 'data:' . $imageInfo['mime'] . ';base64,';
        $base64 = $prefiex.chunk_split(base64_encode($file_content));
        file_put_contents($savePath,$base64);
    }
}