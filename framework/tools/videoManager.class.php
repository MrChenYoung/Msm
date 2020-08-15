<?php


namespace framework\tools;


class videoManager
{
    /**
     * 获取m3u8文件的时长
     * @param $m3u8url m3u8文件路径
     * @return float  获取到的总秒数
     */
    public static function getM3u8Duration($m3u8url){
        $m3u8Content = HttpRequest::getHttpsContent($m3u8url);

        preg_match_all('/.*,/', $m3u8Content, $matches);
        $arr = $matches[0];
        $time = 0;
        foreach ($arr as $item) {
            preg_match('/:(.*),/',$item,$match);
            $time += (float)$match[1];
        }

        return $time;
    }
}