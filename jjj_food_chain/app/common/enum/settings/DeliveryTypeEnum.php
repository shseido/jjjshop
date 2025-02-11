<?php

namespace app\common\enum\settings;

use MyCLabs\Enum\Enum;

/**
 * 配送方式枚举类
 */
class DeliveryTypeEnum extends Enum
{
    // 外卖配送
    const EXPRESS = 10;

    // 上门自提
    const EXTRACT = 20;

    // 打包带走
    const TAKEAWAY = 30;

    // 店内就餐
    const DINNER = 40;

    /**
     * 获取枚举数据
     */
    public static function data($key=0)
    {
        $arr = [
            self::EXPRESS => [
                'name' => __('外卖配送'),
                'value' => self::EXPRESS,
            ],
            self::EXTRACT => [
                'name' => __('到店自提'),
                'value' => self::EXTRACT,
            ],
            self::TAKEAWAY => [
                'name' => __('打包带走'),
                'value' => self::TAKEAWAY,
            ],
            self::DINNER => [
                'name' => __('店内就餐'),
                'value' => self::DINNER,
            ],
        ];
        return $key > 0 ? $arr[$key] : $arr;
    }

    /**
     * 获取外卖数据
     */
    public static function deliver()
    {
        return [
            self::EXPRESS => [
                'name' => __('外卖配送'),
                'value' => self::EXPRESS,
            ],
            self::EXTRACT => [
                'name' => __('到店自提'),
                'value' => self::EXTRACT,
            ],
        ];
    }

    /**
     * 获取店内数据
     */
    public static function store()
    {
        return [
            self::TAKEAWAY => [
                'name' => __('打包带走'),
                'value' => self::TAKEAWAY,
            ],
            self::DINNER => [
                'name' => __('店内就餐'),
                'value' => self::DINNER,
            ],
        ];
    }

    /**
     * 获取外卖数据
     */
    public static function points()
    {
        return [
            self::EXPRESS => [
                'name' => __('快递配送'),
                'value' => self::EXPRESS,
            ],
            self::EXTRACT => [
                'name' => __('到店自提'),
                'value' => self::EXTRACT,
            ],
        ];
    }
}