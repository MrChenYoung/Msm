<?php


namespace framework\tools;


class StringTool
{
    /**
     * 字符串处理:
     * 单引号把字符串包括，如果字符串前后已经有单引号，不作处理
     * @param $str      原字符串
     * @return string   处理过的字符串
     */
    public static function singleQuotesInclude($str){
        $firstChar = substr($str,0,1);
        $lastChar = substr($str, strlen($str) - 1,1);

        $str = $firstChar === "'" ? $str : "'".$str;
        $str = $lastChar === "'" ? $str : $str."'";

        return $str;
    }


    /**
     * 判断字符串是否第一个字符和最后一个字符都是`(反引号)
     * @param $str
     * @return bool
     */
    public static function isFiled($str){
        $firstChar = substr($str,0,1);
        $lastChar = substr($str, strlen($str) - 1,1);
        if ($firstChar == "`" && $lastChar == "`"){
            return true;
        }else {
            return false;
        }
    }

    /**
     * 获取url所有的参数和值
     * @param $url              url
     * @return array|string     参数键值对
     */
    public static function getParams($url){
        $qPosition =strpos($url,"?");
        if ($qPosition === false){
            // url不合法
            return "不合法的url:".$url;
        }else {
            $paraLen = strlen($url) - $qPosition - 1;
            $paramString = substr($url,$qPosition + 1,$paraLen);

            // 获取参数键值对
            $paramArray = explode("&",$paramString);
            $newParamArray = [];
            foreach ($paramArray as $paramKeyValue){
                $keyVArr = explode("=",$paramKeyValue);
                if (count($keyVArr) == 0) continue;

                $key = $keyVArr[0];
                $value = count($keyVArr) > 1 ? $keyVArr[1] : "";
                $newParamArray[$key] = $value;
            }
            return $newParamArray;
        }
    }

    /**
     * 获取url字符串指定键的参数值
     * @param $url              url字符串
     * @param $key              要获取的参数键
     * @return mixed|string     获取给定键的值
     */
    public static function getParamValue($url,$key){
        $params = StringTool::getParams($url);
        if (array_key_exists($key,$params)){
            return $params[$key];
        }else {
            return "";
        }
    }

    /**
     * 去掉字符串的后缀
     * @param $string           源字符串
     * @return false|string     处理后的字符串
     */
    public static function removeExtension($string){
        $suffix = self::getExtension($string);
        return substr($string,0,strlen($string) - strlen($suffix));
    }

    // 获取后缀 返回查找到字符到字符串结尾的子字符串
    public static function getExtension($string){
        $suffix = strrchr($string, '.');
        return $suffix;
    }

    // 保存base64编码的图片
    public function saveBase64Img($base64_image_content,$savePath){
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
            $type = $result[2];
            $path = $savePath;
            if(!file_exists($path))
            {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($path, 0700);
            }
            $fileName = time() . ".{$type}";
            $new_file = $path ."/". $fileName;
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))){
                // 保存成功
                return $fileName;
            } else {
                // 保存失败
                return false;
            }
        }
    }
}