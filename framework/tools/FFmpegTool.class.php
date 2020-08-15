<?php


namespace framework\tools;


class FFmpegTool
{

    /**
     * 创建用于加密ts文件的key文件
     * @param $path key文件路径
     */
    public static function createEncryptKey($path){
        shell_exec("openssl rand  16 > ".$path);
    }

    /**
     * 给视频文件加密切片成ts片段
     * @param $ffmpegPath           ffmpeg安装路径
     * @param $videoPath            视频文件路径
     * @param $tsTime               每个切片时长
     * @param $encryptKeyinfoPath   加密文件路径
     * @param $tsPath               生成的ts分片存储路径
     * @param $m3u8Path             生成的m3u8索引文件存储路径
     * @return bool                 切片是否成功
     */
    public static function spliteVideoEncrypt($ffmpegPath,$videoPath,$tsTime,$encryptKeyinfoPath,$tsPath,$m3u8Path){
        // 加密分片命令
        $cmd = "$ffmpegPath -y -i $videoPath -c:v libx264 -c:a copy -f hls -hls_time $tsTime -hls_list_size 0 -hls_key_info_file $encryptKeyinfoPath -hls_playlist_type vod -hls_segment_filename ".$tsPath." ".$m3u8Path;
        // 执行命令
        putenv('DYLD_LIBRARY_PATH');
        exec($cmd,$result,$status);
        // 结果
        if ($status == 0){
            // 切片成功
            return true;
        }else {
            // 切片失败
            return false;
        }
    }

    /**
     * 获取本地视频文件时长
     * @param $ffmpegPath
     * @param $videoPath
     * @return false|string
     */
    public static function getVideoDuration($ffmpegPath,$videoPath){
        $cmd = $ffmpegPath." -i ".$videoPath." 2>&1 | grep 'Duration' | cut -d ' ' -f 4 | sed s/,//";
        // 执行命令
        putenv('DYLD_LIBRARY_PATH');
        exec($cmd,$result,$status);

        // 结果
        if ($status == 0){
            // 成功

            // 去掉毫秒
            $result = substr($result[0],0,strpos($result[0],"."));

            return $result;
        }else {
            // 切片失败
            return "00:00:00";
        }
    }
}