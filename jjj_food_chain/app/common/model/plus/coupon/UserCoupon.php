<?php

namespace app\common\model\plus\coupon;

//use think\Hook;
use app\common\model\BaseModel;
use app\common\model\plus\coupon\Coupon as CouponModel;
/**
 * 用户优惠券模型
 */
class UserCoupon extends BaseModel
{
    protected $name = 'user_coupon';
    protected $pk = 'user_coupon_id';

    /**
     * 追加字段
     * @var array
     */
    protected $append = ['state'];
    private static $tags = [];

    /**
     * 关联用户表
     */
    public function user()
    {
        return $this->belongsTo('app\common\model\user\User');
    }

    /**
     * 关联优惠券表
     */
    public function coupon()
    {
        return $this->belongsTo('app\common\model\plus\coupon\Coupon');
    }

    /**
     * 关联供应商表
     */
    public function supplier()
    {
        return $this->belongsTo('app\\common\\model\\supplier\\Supplier', 'shop_supplier_id', 'shop_supplier_id')
            ->field(['shop_supplier_id', 'name']);
    }

    /**
     * 优惠券状态
     */
    public function getStateAttr($value, $data)
    {
        if ($data['is_use']) {
            return ['text' => '已使用', 'value' => 0];
        }
        if ($data['is_expire']) {
            return ['text' => '已过期', 'value' => 0];
        }
        return ['text' => '', 'value' => 1];
    }

    /**
     * 优惠券颜色
     */
    public function getColorAttr($value)
    {
        $status = [10 => 'blue', 20 => 'red', 30 => 'violet', 40 => 'yellow'];
        return ['text' => $status[$value], 'value' => $value];
    }

    /**
     * 优惠券类型
     */
    public function getCouponTypeAttr($value)
    {
        $status = [10 => '满减券', 20 => '折扣券'];
        return ['text' => $status[$value], 'value' => $value];
    }

    /**
     * 折扣率
     */
    public function getDiscountAttr($value)
    {
        return $value / 10;
    }

    /**
     * 有效期-开始时间
     */
    public function getStartTimeAttr($value)
    {
        return ['text' => date('Y/m/d', $value), 'value' => $value];
    }

    /**
     * 有效期-结束时间
     */
    public function getEndTimeAttr($value)
    {
        return ['text' => date('Y/m/d', $value), 'value' => $value];
    }

    /**
     * 优惠券详情
     * @param $coupon_id
     * @return array|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function detail($coupon_id)
    {
        return static::find($coupon_id);
    }

    /**
     * 设置优惠券使用状态
     * @param $couponId
     * @param bool $isUse
     * @return UserCoupon
     */
    public static function setIsUse($couponId, $isUse = true)
    {
        $model = new static();
        return $model->where(['user_coupon_id' => $couponId])->update(['is_use' => (int)$isUse]);
    }

    /**
     * 发卡领取优惠券
     * @param $coupon_ids
     * @param $user_id
     */
    public function addUserCardCoupon($coupons, $user, $order_id)
    {
        $model = new CouponModel();
        foreach ($coupons as $item) {
            $coupon = $model->where('coupon_id', '=', $item['coupon_id'])->find();
            // 计算有效期
            if ($coupon['expire_type'] == 10) {
                $start_time = time();
                $end_time = $start_time + ($coupon['expire_day'] * 86400);
            } else {
                $start_time = $coupon['start_time']['value'];
                $end_time = $coupon['end_time']['value'];
            }
            for ($i = 0; $i < $item['number']; $i++) {
                // 整理领取记录
                $data[] = [
                    'coupon_id' => $coupon['coupon_id'],
                    'name' => $coupon['name'],
                    'color' => $coupon['color']['value'],
                    'coupon_type' => $coupon['coupon_type']['value'],
                    'reduce_price' => $coupon['reduce_price'],
                    'discount' => $coupon['discount'],
                    'min_price' => $coupon['min_price'],
                    'expire_type' => $coupon['expire_type'],
                    'expire_day' => $coupon['expire_day'],
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'apply_range' => $coupon['apply_range'],
                    'user_id' => $user['user_id'],
                    'order_id' => $order_id,
                    'shop_supplier_id' => $coupon['shop_supplier_id'],
                    'app_id' => $user['app_id']
                ];
            }
        }
        $this->saveAll($data);
        return true;
    }

    /**
     * 撤销卡作废优惠券
     * @param $coupon_ids
     * @param $user_id
     */
    public function cancelUserCardCoupon($coupons, $user, $order_id)
    {
        foreach ($coupons as $item) {
            $data[] = [
                'data' => ['is_use' => 1],
                'where' => [
                    'coupon_id' => $item['coupon_id'],
                    'user_id' => $user['user_id'],
                    'order_id' => $order_id,
                ],
            ];
        }
        return $this->updateAll($data);
    }

}