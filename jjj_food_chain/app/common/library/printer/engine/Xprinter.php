<?php

namespace app\common\library\printer\engine;

/**
 * 飞鹅打印机API引擎
 */
class Xprinter extends Basics
{
    // 接口路径
    const PATH = '/cgi-bin/print.cgi';

    //网口小票打印机IP，连接端口
    const PRINTER_IP = "";
    const PRINTER_PORT = "9100";

    /**
     * 执行订单打印
     */
    public function printTicket($content, $shopName='')
    {
        $config = json_decode($this->config, true);
        $content = hex2bin($content);
        // 因为打印机识别不了，所以替换日语的长音为 -
        $content = str_replace("ー", "-", $content);
        // 
        try {
            $fp = @fsockopen( $config['IP'] ?? self::PRINTER_IP, $config['PORT'] ??  self::PRINTER_PORT, $errno, $errstr, 3);
            if($fp===false) { //连接打印机出错
                // DOTO 记录日志
                trace("连接打印机出错");
                return false;
            }
            // 初始化打印机 
            fwrite($fp, "\x1B\x40");
            // 
            $content = iconv("UTF-8", "UTF-8//IGNORE", $content);
            $segments = preg_split('/([\p{Thai}\p{Hangul}฿]+)/u', $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            foreach ($segments as $segment) {
                if (preg_match('/[\p{Thai}]/u', $segment)  || strpos($segment, "฿") !== false) {
                    fwrite($fp, "\x1C\x2E");
                    fwrite($fp, iconv("UTF-8", "CP874//IGNORE",  $segment));
                } else if (preg_match('/[\p{Hangul}]/u', $segment)) {
                    fwrite($fp, "\x1C\x26");
                    fwrite($fp, iconv("UTF-8", "CP949//IGNORE",  $segment));
                } else {
                    fwrite($fp, "\x1C\x26");
                    fwrite($fp, iconv("UTF-8", "GBK//IGNORE",  $segment));
                }
            }
            //关闭打印机连接
            fclose($fp);
        } catch (\Exception $e) {
            trace("连接打印机出错");
            return false;
        }
        // 
        return true;
    }


}