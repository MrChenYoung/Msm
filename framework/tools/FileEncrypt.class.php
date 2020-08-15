<?php


namespace framework\tools;

/**
 * Class FileEncrypt 文件加密/解密类
 */
class FileEncrypt
{
    /**
     * 文件对称加密
     * @param $originPath 原始文件路径
     * @param $desPath    加密以后文件存储路径
     */
    static public function encode($originPath,$desPath){
        if (!file_exists($originPath)){
            echo "原始文件不存在";
            die;
        }

        // 文档中建议：为移植性考虑，强烈建议在用 fopen() 打开文件时总是使用 'b' 标记。
        $fileId = fopen($originPath, 'rb+');

        // 取出文件大小的字节数 （29124）
        $fileSize = fileSize($originPath);

        // 读取文件，返回所读取的字符串 （读出来的为二进制序列）
        $img = fread($fileId, $fileSize);

        // 使用“无符号字符”，从二进制字符串对数据进行解包
        // （pack、unpack用法）https://segmentfault.com/a/1190000008305573
        $imgUnpack = unpack('C*', $img); // $fileSize 长度的一维数组 [ 1=&gt;255, 2=&gt;216, 3=&gt;255, ……, 29124=&gt;217 ]

        // 关闭一个已打开的文件指针
        fclose($fileId);

        $tempArr = [];
        // 自定义加密规则
        for ($i = 1; $i <= $fileSize; $i++) {
            $value = 0;
            if ($i % 3 == 0) {
                $value = 2;
            } elseif ($i % 5 == 0) {
                $value = 4;
            } elseif ($i % 7 == 0) {
                $value = 6;
            }
            $byte = $imgUnpack[$i];	// 图片原始字节
            $byte = $byte + $value; // 经过加密规则之后的字节
            // 打包成二进制字符串
            $tempArr[] = pack('C*', $byte);
        }

        $img = implode('', $tempArr);	// 将解包之后的一维数组装换成字符串
        file_put_contents($desPath, $img); // 重写图片
    }


    /**
     * 文件对称解密
     * @param $originPath  原始文件路径
     * @param $desPath     解密以后文件存储路径
     */
    static public function decode($originPath,$desPath){
        if (!file_exists($originPath)){
            echo "原始文件不存在";
            die;
        }

        $fileId = fopen($originPath, 'rb+');
        $fileSize = filesize($originPath);
        $img = fread($fileId, $fileSize);
        $imgUnpack = unpack('C*', $img);
        fclose($fileId);

        $tempArr = [];
        // 开始解密
        for ($i = 1; $i <= $fileSize; $i++) {
            $value = 0;
            if ($i % 3 == 0) {
                $value = 2;
            } elseif ($i % 5 == 0) {
                $value = 4;
            } elseif ($i % 7 == 0) {
                $value = 6;
            }
            $byte = $imgUnpack[$i];
            $byte = $byte - $value;
            $tempArr[] = pack('C*', $byte);
        }
        $img = implode('', $tempArr);
        file_put_contents($desPath, $img);
    }


    /**
     * 图片追加信息
     *
     * @param [string] $filePath	图片路径
     * @param [array] $cardmsg	需要添加的信息数组
     * @param [array] $separate	分隔数组（类似于做一个加密分隔 key）
     * @return void
     */
    static public function encmsg($filePath, $cardmsg, $separate){
        // 文档中建议：为移植性考虑，强烈建议在用 fopen() 打开文件时总是使用 'b' 标记。
        $fileId = fopen($filePath, 'rb+');
        // 取出文件大小的字节数 （29124）
        $fileSize = fileSize($filePath);
        // 读取文件，返回所读取的字符串 （读出来的为二进制序列）
        $img = fread($fileId, $fileSize);
        // 使用“无符号字符”，从二进制字符串对数据进行解包
        // （pack、unpack用法）https://segmentfault.com/a/1190000008305573
        $imgUnpack = unpack('C*', $img); // $fileSize 长度的一维数组 [ 1=&gt;255, 2=&gt;216, 3=&gt;255, ……, 29124=&gt;217 ]
        // 关闭一个已打开的文件指针
        fclose($fileId);

        // 处理身份信息
        $cardmsgJson = json_encode($cardmsg, JSON_UNESCAPED_UNICODE);
        $cardmsgUnpack = unpack('C*', $cardmsgJson);

        // 合并图片字节、自定义分隔数组（类似手动加 key 值）、身份信息字节
        $mergeArr = array_merge($imgUnpack, $separate, $cardmsgUnpack);

        $pack = [];
        foreach ($mergeArr as $k => $v) {
            $pack[] = pack('C*', $v);
        }
        $packStr = join('', $pack);
        file_put_contents($filePath, $packStr); // 重写图片
    }


    /**
     * 获取追加进图片的信息
     *
     * @param [string] $filePath	图片路径
     * @param [array] $separate	定义的分隔数组（分隔 key）
     * @return [string] 追加进的图片信息
     */
    static public function decmsg ($filePath, $separate){
        // 文档中建议：为移植性考虑，强烈建议在用 fopen() 打开文件时总是使用 'b' 标记。
        $fileId = fopen($filePath, 'rb+');
        // 取出文件大小的字节数 (29192)
        $fileSize = fileSize($filePath);
        // 读取文件，返回所读取的字符串 （读出来的为二进制序列）
        $img = fread($fileId, $fileSize);

        // 使用“无符号字符”，从二进制字符串对数据进行解包
        $imgUnpack = unpack('C*', $img); // $fileSize 长度的一维数组 [ 1=&gt;255, 2=&gt;216, 3=&gt;255, ……, 29192=&gt;217 ]
        // 关闭一个已打开的文件指针
        fclose($fileId);

        $imgUnpackStr = join(',',$imgUnpack); // 将一维数组转换为字符串
        $separateStr = implode(',', $separate); // 将一维数组转换为字符串
        $imgAndCardmsgArr = explode($separateStr, $imgUnpackStr); // 以自定义分隔符分隔出图片字节和身份信息字节

        $cardmsgArr = explode(',', $imgAndCardmsgArr[1]); // 取出身份信息字节
        unset($cardmsgArr[0]); // 去除身份信息字节首位空白 （字符串转数组时所留）
        $cardmsg = '';
        foreach ($cardmsgArr as $k => $v) {
            $cardmsg .= pack('C*', $v);	// 打包成二进制文件字符串
        }

        return json_decode($cardmsg, true);
    }


}