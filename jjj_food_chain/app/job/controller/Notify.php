<?php

namespace app\job\controller;


use app\common\library\alipay\AliPay;
use app\common\library\easywechat\WxPay;
use app\job\model\order\Order as OrderModel;

/**
 * 微信支付回调
 */
class Notify
{
    /**
     * 微信支付回调
     */
    public function wxpay()
    {
        // 微信支付组件：验证异步通知
        $WxPay = new WxPay(false);
        $WxPay->notify();
    }

    /**
     * 支付宝支付回调（同步）
     */
    public function alipay_return()
    {
        $AliPay = new AliPay();
        $url = $AliPay->return();
        if ($url) {
            return redirect($url);
        }
    }

    /**
     * 支付宝支付回调（异步）
     */
    public function alipay_notify()
    {
        $AliPay = new AliPay();
        $AliPay->notify();
    }

    /**
     * 达达回调（异步）
     */
    public function dada_notify()
    {
        $content = file_get_contents("php://input");
        if ($content) {
            $data = json_decode($content, true);
            $model = new OrderModel;
            $model->dadaOrder($data);
            return json(['message' => 'ok']);
        }
    }

    /**
     * 美团回调（异步）
     */
    public function meituan_notify()
    {
        $content = file_get_contents("php://input");
        if ($content) {
            $data = json_decode($content, true);
            $model = new OrderModel;
            $status = $model->metuanOrder($data);
            if ($status) {
                return json(['code' => 0]);
            }
        }
    }

    /**
     * UU回调（异步）
     */
    public function uu_notify()
    {
        $content = file_get_contents("php://input");
        if ($content) {
            $data = json_decode($content, true);
            $model = new OrderModel;
            $model->uuOrder($data);
        }
    }
}
