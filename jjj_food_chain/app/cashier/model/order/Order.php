<?php

namespace app\cashier\model\order;

use app\common\library\helper;
use app\shop\model\product\Category;
use app\api\model\order\OrderProduct;
use app\common\model\supplier\Supplier;
use app\common\enum\order\OrderTypeEnum;
use app\common\enum\settings\SettingEnum;
use app\common\enum\order\OrderSourceEnum;
use app\common\enum\order\OrderStatusEnum;
use app\common\enum\order\OrderPayTypeEnum;
use app\common\model\user\User as UserModel;
use app\common\enum\order\OrderPayStatusEnum;
use app\common\model\order\OrderProductReturn;
use app\common\model\order\Order as OrderModel;
use app\cashier\model\store\Table as TableModel;
use app\common\enum\product\DeductStockTypeEnum;
use app\common\service\order\OrderRefundService;
use app\common\service\order\OrderCompleteService;
use app\shop\model\user\PointsLog as PointsLogModel;
use app\common\enum\user\pointsLog\PointsLogSceneEnum;
use app\common\model\settings\Setting as SettingModel;
use app\common\service\product\factory\ProductFactory;
use app\common\model\order\OrderBuffet as OrderBuffetModel;
use app\common\model\order\OrderProduct as OrderProductModel;
use app\cashier\service\order\paysuccess\type\MasterPaySuccessService;

/**
 * 普通订单模型
 */
class Order extends OrderModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'update_time',
    ];

    /**
     * 用户中心订单列表 time_type
     */
    public function getList($params)
    {
        $model = $this;
        if (isset($params['shop_supplier_id']) && $params['shop_supplier_id']) {
            $model = $model->where('shop_supplier_id', '=', $params['shop_supplier_id']);
        }
        if (isset($params['eat_type']) && $params['eat_type']) {
            $model = $model->where('eat_type', '=', $params['eat_type']);
        }
        if (isset($params['search']) && $params['search']) {
            $model = $model->like('order_no', $params['search']);
        }
        if (isset($params['order_type']) && $params['order_type']) {
            $model = $model->where('order_type', '=', $params['eat_type']);
        }


        $startTime = 0;
        $endTime = 0;
        //查询时间
        switch ($params['time_type'] ?? 1) {
            case '1'://今天
                $startTime = strtotime(date('Y-m-d'));
                $endTime = $startTime + 86399;
                break;
            case '2'://昨天
                $startTime = strtotime("-1 days", strtotime(date('Y-m-d')));
                $endTime = $startTime + 86399;
                break;
            case '3'://一周
                $startTime = strtotime("-7 days", strtotime(date('Y-m-d')));
                $endTime = time();
                break;
        }
        if (isset($params['time']) && $params['time']) {
            if ($params['time'][0] && $params['time'][1]) {
                $startTime = strtotime($params['time'][0]);
                $endTime = strtotime($params['time'][1]);
                if ($startTime == $endTime) {
                    $endTime = $startTime + 86399;
                }
                $model = $model->where('create_time', 'between', [$startTime, $endTime]);
            } else if ($params['time'][0]) {
                $startTime = strtotime($params['time'][0]);
                $model = $model->where('create_time', '>', $startTime);
            } else if ($params['time'][1]) {
                $endTime = strtotime($params['time'][1]);
                $model = $model->where('create_time', '<', $endTime);
            } else {
                $model = $model->where('create_time', 'between', [$startTime, $endTime]);
            }

        } else if($startTime && $endTime) {
            $model = $model->where('create_time', 'between', [$startTime, $endTime]);
        }

        switch ($params['dataType'] ?? 1) {
            case '1'://进行中
                $model = $model->where('order_status', '=', 10);
                break;
            case '2'://已完成
                $model = $model->where('order_status', '=', 30);
                break;
            case '3'://已取消
                $model = $model->where('order_status', '=', 20);
                break;
        }

        return $model->with(['product.image', 'supplier'])
            ->where('is_delete', '=', 0)
            ->where('delivery_type', 'in', [30, 40])
            ->where('eat_type', '<>', 0)
            ->where('extra_times', '>', 0)
            ->order(['create_time' => 'desc'])
            ->field("*,FROM_UNIXTIME(pay_time,'%Y-%m-%d %H:%i:%s') as pay_time_text ")
            ->paginate($params);
    }

    // 订单统计信息
    public function getInfo($params)
    {
        $pendingNum = (new self)
            ->where('extra_times', '>', 0)
            ->where('is_delete', '=', 0)
            ->where('delivery_type', 'in', [30, 40])
            ->where('eat_type', '<>', 0)
            ->where('order_status', '=', OrderStatusEnum::NORMAL)
            ->count();
        $cancelNum = (new self)
            ->where('extra_times', '>', 0)
            ->where('is_delete', '=', 0)
            ->where('delivery_type', 'in', [30, 40])
            ->where('eat_type', '<>', 0)
            ->where('order_status', '=', OrderStatusEnum::CANCELLED)
            ->count();
        $completeNum = (new self)
            ->where('extra_times', '>', 0)
            ->where('is_delete', '=', 0)
            ->where('delivery_type', 'in', [30, 40])
            ->where('eat_type', '<>', 0)
            ->where('order_status', '=', OrderStatusEnum::COMPLETED)
            ->count();
        return compact('pendingNum', 'cancelNum', 'completeNum');
    }

    /**
     * 标记订单已支付
     */
    public function onPayment($orderNo, $pay_type)
    {
        // 获取订单详情
        $PaySuccess = new MasterPaySuccessService($orderNo);
        // 发起余额支付
        $this->startTrans();
        try {
            // 付款减库存-判断库存
            $error = [];
            foreach ($this->product as $product) {
                if ($product['deduct_stock_type'] == DeductStockTypeEnum::PAYMENT) {
                    $stockStatus = $product->getStockState($product['total_num']);
                    if (!$stockStatus) {
                        $error[] = [
                            'order_product_id' => $product['order_product_id'],
                            'product_id' => $product['product_id'],
                            'product_sku_id' => $product['product_sku_id'],
                            'total_num' => $product['total_num'],
                            'product_name_text' => $product['product_name_text'],
                        ];
                        continue;
                    }
                }
            }
            if (!empty($error)) {
                $this->error = "商品库存不足，请重新选择";
                $this->errorData = $error;
                return false;
            }
            // 订单商品送厨
            $model = new OrderProductModel();
            if (!$model->sendKitchen($this['order_id'], 'payment')) {
                $this->error = $model->getError();
                $this->errorData = $model->getErrorData();
                $this->errorCode = $model->getErrorCode();
                return false;
            }
            // 如果是自助餐，给自助餐增销量
            if ($this['is_buffet'] == 1) {
               $orderBuffets = OrderBuffetModel::where('order_id', $this['order_id'])->select();
                if ($orderBuffets) {
                    foreach ($orderBuffets as $orderBuffet) {
                        $orderBuffet->buffet()->setInc('sale_num', $orderBuffet['num']);
                    }
                }
            }
            //
            $status = $PaySuccess->onPaySuccess($pay_type);
            if (!$status) {
                $this->error = $PaySuccess->getError();
                return false;
            }
            $this->commit();
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
        //
        return $status;
    }

    /**
     * 取消订单
     */
    public function cancel($table_id)
    {
        $detail = $this->where('table_id', '=', $table_id)
            ->where('order_status', '=', 10)
            ->where('eat_type', '=', 10)
            ->find();

        if (!$detail) {
            TableModel::close($table_id);
            $this->error = "订单不存在";
            return false;
        }

        if ($detail['pay_status']['value'] == 20) {
            TableModel::close($table_id);
            $this->error = "订单已付款，不允许取消";
            return false;
        }
        if ($detail['order_status']['value'] != 10) {
            $this->error = "订单状态错误，不允许取消";
            return false;
        }
        return $detail->save(['order_status' => 20]);
    }


    /**
     * 取消订单
     */
    public function cancels()
    {
        if ($this->pay_status['value'] == 20) {
            $this->error = "订单已付款，不允许取消";
            return false;
        }
        if ($this->order_status['value'] != 10) {
            $this->error = "订单状态错误，不允许取消";
            return false;
        }
        return $this->save(['order_status' => 20]);
    }

    // 取消开台订单
    public function CashierOrderCancels()
    {
        if ($this->pay_status['value'] == 20) {
            $this->error = "订单已付款，不允许取消";
            return false;
        }
        return $this->save(['order_status' => 20]);
    }

    /**
     * 待支付订单详情
     */
    public static function getPayDetail($orderNo)
    {
        $model = new static();
        return $model->where(['order_no' => $orderNo, 'pay_status' => 10, 'is_delete' => 0])->with(['product', 'user', 'supplier'])->find();
    }

    /**
     * 设置错误信息
     */
    protected function setError($error)
    {
        empty($this->error) && $this->error = $error;
    }

    /**
     * 是否存在错误
     */
    public function hasError()
    {
        return !empty($this->error);
    }

    /**
     * 主订单购买的数量
     * 未取消的订单
     */
    public static function getHasBuyOrderNum($user_id, $product_id)
    {
        $model = new static();
        return $model->alias('order')->where('order.user_id', '=', $user_id)
            ->join('order_product', 'order_product.order_id = order.order_id', 'left')
            ->where('order_product.product_id', '=', $product_id)
            ->where('order.order_source', '=', OrderSourceEnum::MASTER)
            ->where('order.order_status', '<>', 21)
            ->sum('total_num');
    }

    //查询桌号信息
    public static function getTableInfo($table_id)
    {
        return (new static())->where('table_id', '=', $table_id)
            ->where('is_delete', '=', 0)
            ->order('order_id desc')
            ->find();
    }

    //查询桌号订单信息
    public static function getOrderInfo($table_id)
    {
        return (new static())->with('product')
            ->where('table_id', '=', $table_id)
            ->where('order_status', '=', 10)
            ->where('is_delete', '=', 0)
            ->find();
    }

    /**
     * 折扣抹零
     */
    public function changeMoney($user, $data)
    {
        if (isset($data['order_id']) && $data['order_id'] > 0) {
            $detail = OrderModel::detail([
                ['order_id', '=', $data['order_id']],
                ['order_status', '=', OrderStatusEnum::NORMAL]
            ]);
        } else if (isset($data['table_id']) && $data['table_id'] > 0) {
            $detail = self::getTableUnderwayOrder($data['table_id']);
        } else {
            $detail = null;
        }

        // 检查订单状态
        if (!$detail) {
            $this->error = '当前状态不可操作';
            return false;
        }

        if ($detail['pay_status']['value'] != 10) {
            $this->error = "订单已支付不允许改价";
            return false;
        }
        $this->startTrans();
        try {
            $discount_money = 0;
            $discount_ratio = 0;
            switch ($data['type']) {
                case '1'://改价
                    if ($data['money'] > 999999999 || $data['money'] < 0) {
                        $this->error = "价格范围错误";
                        return false;
                    }
                    $discount_money = round($detail['order_price'] - $data['money'], 2);
                    $discount_money = max($discount_money, 0);
                    break;
                case '2'://折扣
                    if ($data['rate'] < 0 || $data['rate'] > 100) {
                        $this->error = "请输入合理的折扣";
                        return false;
                    }
                    if ($data['rate'] < 1) {
                        $discount_ratio = -1;
                    } else {
                        $discount_ratio = $data['rate'];
                    }

                    break;
                case '3'://抹零

                    break;
            }

            if ($data['type'] == 2) {
                $detail->save(['discount_ratio' => $discount_ratio, 'is_change_price' => 1]);
                (new OrderModel())->reloadPrice($detail['order_id']);
            } else if ($data['type'] == 3) {
                // 折扣抹零重置 （恢复旧版抹零就把这个判断删了。上面的case 3 注释恢复）
                $detail->save(['discount_ratio' => 0, 'discount_money' => 0]);
                $o = (new OrderModel())->reloadPrice($detail['order_id']);
                if ($data['discountType'] == 1) { //抹分
                    $discount_money = round($o['order_price'] - intval($o['pay_price'] * 10) / 10, 2);
                } elseif ($data['discountType'] == 2) { //抹角
                    $discount_money = round($o['order_price'] - intval($o['pay_price']), 2);
                } elseif ($data['discountType'] == 3) { //四舍五入到角
                    $discount_money = round($o['order_price'] - round($o['pay_price'], 1), 2);
                } elseif ($data['discountType'] == 4) { //四舍五入到元
                    $discount_money = round($o['order_price'] - round($o['pay_price'], 0), 2);
                }
                //
                $pay_price = round($o['order_price'] - $discount_money, 2);
                // 积分奖励按照应付计算
                $setting = SettingModel::getSupplierItem(SettingEnum::POINTS, $detail['shop_supplier_id'], $detail['app_id']);
                if ($setting['is_shopping_gift']) {
                    // 积分赠送比例
                    $ratio = $setting['gift_ratio'] / 100;
                } else {
                    $ratio = 1;
                }
                $points_bonus = helper::bcmul($pay_price, $ratio, 3);
                $points_bonus = round($points_bonus, 2);
                //
                $o->save([
                    'discount_money' => $discount_money < 0 ? 0 : $discount_money,
                    'pay_price' => $pay_price,
                    'points_bonus' => $points_bonus,
                    'is_change_price' => 1
                ]);

            } else {
                if ($data['money'] > $detail['order_price']) {
                    $pay_price = $data['money'];
                } else {
                    $pay_price = round($detail['order_price'] - $discount_money, 2);
                }
                if ($pay_price <= 0) {
                    $pay_price = 0;
                }

                //
                if ($data['type'] == 1) {
                    // 积分奖励按照应付计算
                    $setting = SettingModel::getSupplierItem(SettingEnum::POINTS, $detail['shop_supplier_id'], $detail['app_id']);
                    if ($setting['is_shopping_gift']) {
                        // 积分赠送比例
                        $ratio = $setting['gift_ratio'] / 100;
                    } else {
                        $ratio = 1;
                    }
                    $points_bonus = helper::bcmul($pay_price, $ratio, 3);
                    $points_bonus = round($points_bonus, 2);
                    $detail->save([
                        'discount_money' => $discount_money < 0 ? 0 : $discount_money,
                        'pay_price' => $pay_price,
                        'discount_ratio' => $discount_ratio,
                        'user_discount_money' => 0,
                        'points_bonus' => $points_bonus,
                        'is_change_price' => 1
                    ]);
                } else {
                    $detail->save([
                        'discount_money' => $discount_money < 0 ? 0 : $discount_money,
                        'pay_price' => $pay_price,
                        'is_change_price' => 1
                    ]);
                }

            }

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    //查询桌号信息
    public function changeTable($table_id)
    {
        if ($this['order_status']['value'] != 10) {
            $this->error = "订单状态错误，不允许转台";
            return false;
        }
        $orderInfo = self::getTableInfo($table_id);
        if ($orderInfo) {
            if ($orderInfo['order_source'] == 10) {//小程序下单
                if ($orderInfo['pay_status']['value'] == 20 && $orderInfo['order_status']['value'] == 10) {
                    $this->error = "台号已被使用";
                    return false;
                }
            } else {//收银台下单
                if ($orderInfo['order_status']['value'] == 10) {
                    $this->error = "台号已被使用";
                    return false;
                }
            }
        }
        return $this->save(['table_id' => $table_id]);
    }

    // 交换桌台(转台)
    public function exchangeTable($old_table_id, $new_table_id)
    {
        if ($this['order_status']['value'] != 10) {
            $this->error = "订单状态错误，不允许转台";
            return false;
        }
        $newTable = TableModel::detail($new_table_id);

        $this->startTrans();
        try {
            $this->save(['table_id' => $new_table_id, 'table_no' => $newTable['table_no']]);
            TableModel::open($new_table_id);
            TableModel::close($old_table_id);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }

    }

    // 订单支付
    public function orderPay($data, $cashier = null)
    {
        if ($this['pay_status']['value'] != 10) {
            $this->error = "订单已支付";
            return false;
        }
        if (isset($data['user_id']) && $data['user_id'] > 0) {
            $this->save(['user_id' => $data['user_id']]);
        }
        if ($cashier && isset($cashier['cashier_id'])) {
            $this->save(['cashier_id' => $cashier['cashier_id']]);
        }
        return $this->onPayment($this['order_no'], $data['pay_type']);
    }

    //退菜
    public function moveProduct($order_product_id, $num, $return_reason = '')
    {
        if ($this['order_status']['value'] != 10) {
            $this->error = "订单已完成,不允许退菜";
            return false;
        }

        $orderProduct = OrderProduct::detail($order_product_id);
        if (!$orderProduct) {
            $this->error = "当前状态不可操作";
            return false;
        }

        if ($orderProduct['total_num'] < $num) {
            $this->error = "退菜数量不能大于当前商品数量";
            return false;
        }
        $this->startTrans();
        try {
            $isPay = $this['pay_status']['value'] == 20 ? 1 : 0;
            // 退回商品库存
            ProductFactory::getFactory($this['order_source'])->backProductStock([$orderProduct], $isPay);
            if ($orderProduct['total_num'] == $num) {
                $orderProduct->force()->delete();
            } else {
                $total_num = $orderProduct['total_num'] - $num;
                $orderProduct->save([
                    'total_num' => $total_num,
                ]);
            }
            // 退菜记录
            if ($num > 0) {
                OrderProductReturn::add([
                    'order_id' => $this['order_id'],
                    'order_product_id' => $order_product_id,
                    'product_id' => $orderProduct['product_id'],
                    'num' => $num,
                    'reason' => $return_reason,
                ]);
            }
            //
            $this->reloadPrice($this['order_id']);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    //结账完成
    public function settle()
    {
        if ($this['pay_status']['value'] != 20) {
            $this->error = "订单未付款，不允许操作";
            return false;
        }
        if ($this['order_status']['value'] != 10) {
            $this->error = "订单状态错误，不允许操作";
            return false;
        }
        return $this->transaction(function () {
            // 更新订单状态：已发货、已收货
            $status = $this->save([
                'delivery_status' => 20,
                'delivery_time' => time(),
                'receipt_status' => 20,
                'receipt_time' => time(),
                'order_status' => OrderStatusEnum::COMPLETED
            ]);
            // 执行订单完成后的操作
            $OrderCompleteService = new OrderCompleteService(OrderTypeEnum::MASTER);
            $OrderCompleteService->complete([$this], static::$app_id);
            return $status;
        });
    }

    /**
     * 审核：用户取消订单
     */
    public function refund($data)
    {
        // 判断订单是否有效
        if ($this['pay_status']['value'] != 20 ) {
            $this->error = '该订单不合法';
            return false;
        }
        if ($data['refund_money'] + $this['refund_money'] > $this['pay_price']) {
            $this->error = '退款金额不能大于可退款金额';
            return false;
        }
        // 订单取消事件
        $status = $this->transaction(function () use ($data) {
            // 执行退款操作
            $this['pay_type']['value'] < 40 && (new OrderRefundService)->execute($this, $data['refund_money']);
            $update['refund_money'] = $this['refund_money'] + $data['refund_money'];
            if ($update['refund_money'] == $this['pay_price']) {
                $update['delivery_status'] = 20;
                $update['delivery_time'] = time();
                $update['receipt_status'] = 20;
                $update['receipt_time'] = time();
                $update['order_status'] = 30;
            }
            // 更新账户积分
            $ratio = helper::bcdiv($this['points_bonus'], $this['pay_price']);
            $points = helper::bcmul($data['refund_money'], $ratio, 2); // 应扣除积分
            //
            $user = UserModel::where('user_id', '=', $this['user_id'])->find();
            if ($user) {
                $diffMoney = $user?->points - $data['refund_money'] <= 0 ? 0 : $user?->points - $data['refund_money'];
                $countPoints = -$data['refund_money'];
                $totalPoints = $user?->total_points + $countPoints <= 0 ? 0 : $user?->total_points + $countPoints;
                $user?->update([
                    'points' => $diffMoney,
                    'total_points' => $totalPoints
                ]);
            }
            PointsLogModel::add([
                'user_id' => $this['user_id'],
                'card_id' => $user?->card_id ?? 0,
                'scene' => PointsLogSceneEnum::REFUND,
                'value' => -$points,
                'describe' => "退款扣除：{$this['order_no']}",
                'remark' => '',
            ]);
            // 更新订单状态
            return $this->save($update);
        });
        return $status;
    }

    /**
     * 营业数据
     */
    public function businessData($params)
    {
        $categoryType = $params['category_type'] ?? 1;
        $shopSupplierId = $params['shop_supplier_id'] ?? 0;
        //
        $startTime = 0;
        $endTime = 0;
        //查询时间
        switch ($params['time_type'] ?? 1) {
            case '1'://今天
                $startTime = strtotime(date('Y-m-d'));
                $endTime = $startTime + 86399;
                break;
            case '2'://昨天
                $startTime = strtotime("-1 days", strtotime(date('Y-m-d')));
                $endTime = $startTime + 86399;
                break;
            case '3'://一周
                $startTime = strtotime("-7 days", strtotime(date('Y-m-d')));
                $endTime = time();
                break;
        }
        if (isset($params['time']) && $params['time'] && ($params['time'][0] ?? '') && ($params['time'][1] ?? '')) {
            $startTime = strtotime($params['time'][0]);
            $endTime = strtotime($params['time'][1]) + 86399;
        }
        //
        $model = $this->alias('a')
            ->where('a.pay_status', '=', OrderPayStatusEnum::SUCCESS)
            ->where('a.order_status', '=', OrderStatusEnum::COMPLETED)
            ->where('a.eat_type', '<>', 0)
            ->when( $shopSupplierId , function($q) use($shopSupplierId) {
                $q->where('a.shop_supplier_id', '=', $shopSupplierId);
            })
            ->when( $startTime && $endTime , function($q) use($startTime, $endTime) {
                $q->where('a.create_time', 'between', [$startTime, $endTime]);
            });
        //
        $categorys = $model->clone()
            ->leftJoin('order_product rp','a.order_id = rp.order_id')
            ->leftJoin('product p','p.product_id = rp.product_id')
            ->leftJoin('category c','c.category_id = p.category_id')
            ->when( $categoryType , function($q) use($categoryType) {
                if ($categoryType == 1) {
                    $q->leftJoin('category cc', 'cc.category_id = IF(c.parent_id = 0, c.category_id, c.parent_id)');
                    $q->where('cc.parent_id', 0);
                    $q->group('cc.category_id');
                    $q->field('cc.category_id, cc.name');
                } else {
                    $q->where('c.parent_id', '>', 0);
                    $q->group('c.category_id');
                    $q->field('c.category_id, c.name');
                }
            })
            ->field("sum(rp.total_num) as sales, sum(rp.total_pay_price) as prices")
            ->select()
            ->append([])?->toArray();
        foreach ($categorys as $key => &$data){
            $data['parent_id'] =  $categoryType == 1 ? 0 : Category::where('category_id', $data['category_id'])->value('parent_id');
            $categorys[$key]['name_text'] = Category::getPathNameTextAttr($data['name'] ?: '', $data);
        }
        //
        $incomes = [];
        $values = $model->clone()->group("a.pay_type")->field("a.pay_type,sum(a.pay_price - a.refund_money) as price")->select()?->append([]) ?? [];
        foreach ($values as $value){
            if ($value['price'] > 0) {
                $incomes[] = [
                    'pay_type' => $value['pay_type']['value'],
                    'pay_type_name' => OrderPayTypeEnum::data($value['pay_type']['value'], 2)['name'],
                    'price' => $value['price'],
                ];
            }
        }
        //
        return [
            'supplier' => Supplier::field('shop_supplier_id,business_id,name,address,description,link_name,link_phone,logo,app_id')
                ->where('shop_supplier_id', $shopSupplierId )
                ->find()?->toArray(),
            'categorys' => $categorys,
            'sales_num' => $model->clone()->count(),
            'incomes' => $incomes,
            'refund_amount' => number_format($model->clone()->sum("refund_money"), 2, '.', ''),
            'total_amount' => number_format($model->clone()->sum("pay_price"), 2, '.', ''),
            'times' => [$startTime, $endTime],
        ];
    }

    // 修改订单商品价格
    public function changeProductPrice($order_product_id, $money)
    {
        $this->startTrans();
        try {
            if ($money < 0) {
                $this->error = "价格错误";
                return false;
            }
            $p = OrderProduct::where('order_product_id', '=', $order_product_id)->find();
            if (!$p) {
                $this->error = "商品不存在";
                return false;
            }
            $p->product_price = $money;
            $p->total_price = helper::bcmul($money, $p->total_num);
            if ($p->save()) {
                // 更新
                $this->reloadPrice($this['order_id']);
                $this->commit();
                return true;
            } else {
                $this->error = "商品不存在";
                return false;
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }

    }

    // 更新商品总价
    public function updateTotalPrice()
    {
        // 商品总价 - 优惠抵扣
        $total_price = 0;
        foreach ($this['product'] as $product) {
            $total_price = helper::bcadd($total_price, $product['total_price']);
        }
        $order_price = helper::bcadd($total_price, $this['service_money']);
        $pay_price = round(helper::bcsub($order_price, $this['discount_money']), 2);
        return $this->save(['total_price' => $total_price, 'order_price' => $order_price, 'pay_price' => $pay_price]);
    }

    // 订单使用会员
    public function useMember($user_id)
    {
        $this->startTrans();
        try {
            // 订单表更新user_id
            $user_id = !empty($user_id) ? $user_id : 0;
            $this->save(['user_id' => $user_id, 'is_change_price' => 0]);
            // 重载订单价格信息
            $this->reloadPrice($this['order_id']);
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    // 整单取消
    public function delStay($order_id)
    {
        // 检查订单状态
        $detail = OrderModel::detail([
            ['order_id', '=', $order_id],
            ['order_status', '=', OrderStatusEnum::NORMAL]
        ]);

        if (!$detail) {
            $this->error = '当前状态不可操作';
            return false;
        }

        $this->startTrans();
        try {
            $detail = Order::detail($order_id);
            $force = $detail['extra_times'] <= 0;
            // 获取订单产品
            $orderProducts = OrderProduct::where('order_id', '=', $order_id)->select();
            foreach ($orderProducts as $orderProduct) {
                // 如果未送厨，强制删除
                if ($orderProduct['is_send_kitchen'] == 0) {
                    $orderProduct->force()->delete();
                } else {
                    // 如果已送厨，软删除
                    $orderProduct->delete();
                }
            }
            //
            if ($force) {
                $detail->force()->delete();
            }else{
                $detail->CashierOrderCancels();
            }
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    // 挂单列表
    public function getStayList()
    {
        return $this->with(['product'])->where('is_stay', '=', 1)->where('order_status', '=', OrderStatusEnum::NORMAL)->select();
    }

    // 订单挂单
    public function stayOrder($order_id)
    {
        // 检查订单状态
        $detail = OrderModel::detail([
            ['order_id', '=', $order_id],
            ['order_status', '=', OrderStatusEnum::NORMAL]
        ]);
        if (!$detail) {
            $this->error = '当前状态不可操作';
            return false;
        }
        return $this->where('order_id', '=', $order_id)->update(['is_stay' => 1, 'stay_time' => time()]);
    }

    // 订单取单
    public function pickOrder($order_id)
    {
        // 检查订单状态
        $detail = OrderModel::detail([
            ['order_id', '=', $order_id],
            ['order_status', '=', OrderStatusEnum::NORMAL]
        ]);
        if (!$detail) {
            $this->error = '当前状态不可操作';
            return false;
        }
        return $this->where('order_id', '=', $order_id)->update(['is_stay' => 0]);
    }

    // 订单挂单数量
    public function stayOrderNum()
    {
        return $this->where('is_stay', '=', 1)->where('order_status', '=', 10)->count();
    }


}