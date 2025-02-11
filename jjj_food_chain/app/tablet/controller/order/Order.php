<?php

namespace app\tablet\controller\order;

use app\common\enum\settings\SettingEnum;
use app\common\library\helper;
use app\common\model\buffet\Buffet;
use app\common\model\order\OrderProduct;
use app\common\model\settings\Setting as SettingModel;
use app\tablet\model\store\Table as TableModel;
use app\cashier\service\order\settled\CashierOrderSettledService;
use app\tablet\model\order\Cart as CartModel;
use app\tablet\model\order\Order as OrderModel;
use app\tablet\controller\Controller;
use hg\apidoc\annotation as Apidoc;
/**
 * 点餐
 * @Apidoc\Group("order")
 * @Apidoc\Sort(1)
 */
class Order extends Controller
{
    /**
     * @Apidoc\Title("桌台开台")
     * @Apidoc\Method("POST")
     * @Apidoc\Url ("/index.php/tablet/order.Order/tableBuy")
     * @Apidoc\Param("table_id", type="int", require=true, desc="桌台ID")
     * @Apidoc\Param("meal_num", type="int", require=true, desc="就餐人数")
     * @Apidoc\Param("is_buffet", type="int", require=true, desc="是否自助餐 0-否 1-是")
     * @Apidoc\Param("user_id", type="int", require=false, desc="会员ID（会员自己开台必填）")
     * @Apidoc\Returned()
     */
    public function tableBuy()
    {
        // 立即购买：获取订单商品列表
        $params = $this->postData();
        $params['eat_type'] = 10;
        $params['order_source'] = 10;
        if ($params['meal_num'] > 999 || $params['meal_num'] < 1) {
            return $this->renderError('请输入1-999的人数');
        }
        $table = TableModel::detail($params['table_id']);
        if (!$table) {
            return $this->renderError('桌台不存在');
        }
        if ($table['status'] == 30) {
            return $this->renderError('桌台已开台');
        }
        // 自助餐
        if (($params['is_buffet'] ?? 0) == 1) {
            // 自助餐设置
            $buffetSetting = SettingModel::getSupplierItem(SettingEnum::BUFFET, $this->table['shop_supplier_id'] ?? 0, $this->table['app_id'] ?? 0);
            if ($buffetSetting['is_open'] != 1) {
                return $this->renderError('未开启自助餐');
            }
            if (empty($params['buffet_ids'])) {
                return $this->renderError('请选择自助餐');
            }
        }

        // 实例化订单service
        $user = [
            'cashier_id' => 0,
            'user_name' => '',
            'account' => '',
            'mobile' => NULL,
            'shop_supplier_id' => $this->table['shop_supplier_id'],
            'name' => '',
        ];
        $orderService = new CashierOrderSettledService($user, [], $params);
        // 订单信息初始化
        $orderInfo = $orderService->settlement();
        if ($orderService->hasError()) {
            return $this->renderError($orderService->getError());
        }
        // 创建订单
        $order_id = $orderService->createOrder($orderInfo);
        if (!$order_id) {
            return $this->renderError($orderService->getError() ?: '订单创建失败');
        }
        (new OrderModel())->reloadPrice($order_id);
       // 修改桌台状态
        TableModel::open($params['table_id']);
        // 返回结算信息
        return $this->renderSuccess('开台成功', ['order_id' => $order_id]);
    }

    /**
     * @Apidoc\Title("桌台未下单商品")
     * @Apidoc\Method("POST")
     * @Apidoc\Url ("/index.php/tablet/order.Order/getUnSendKitchen")
     * @Apidoc\Param("table_id", type="int", require=true, desc="桌台ID")
     * @Apidoc\Returned("list",type="array",ref="app\tablet\model\order\Order\getUnSendKitchen")
     */
    public function getUnSendKitchen($table_id)
    {
        $model = new OrderModel();
        $detail = $model->getUnSendKitchen($table_id);
        if ($detail) {
            $detail['unSendKitchenProductTotalPrice'] = helper::getArrayColumnSum($detail['unSendKitchenProduct'], 'total_price');
            $detail['unSendKitchenProductTotalNum'] = helper::getArrayColumnSum($detail['unSendKitchenProduct'], 'total_num');
            $detail['totalPrice'] = helper::getArrayColumnSum($detail['product'], 'total_price');
            $totalBuffetNum = helper::getArrayColumnSum($detail['buffet'], 'num');
            $totalDelayNum = helper::getArrayColumnSum($detail['delay'], 'num');
            $totalProductNum = helper::getArrayColumnSum($detail['product'], 'total_num');
            $detail['totalNum'] = $totalBuffetNum + $totalDelayNum + $totalProductNum;
        }
        return $this->renderSuccess('', compact('detail'));
    }

    /**
     * @Apidoc\Title("桌台已下单商品")
     * @Apidoc\Method("POST")
     * @Apidoc\Url ("/index.php/tablet/order.Order/getSendKitchen")
     * @Apidoc\Param("table_id", type="int", require=true, desc="桌台ID")
     * @Apidoc\Returned("list",type="array",ref="app\tablet\model\order\Order\getSendKitchen")
     */
    public function getSendKitchen($table_id)
    {
        $model = new OrderModel();
        $order = $model->getSendKitchen($table_id);
        $buffet = $order['buffet'];
        $delay = $order['delay'];
        $list = [];
        $total_price = 0;

        if ($order) {
            $buffetTotalPrice = helper::getArrayColumnSum($order['buffet'], 'total_price');
            $delayTotalPrice = helper::getArrayColumnSum($order['delay'], 'total_price');
            $sendKitchenProductTotalPrice = helper::getArrayColumnSum($order['sendKitchenProduct'], 'total_product_price');
            $total_price = helper::bcadd(helper::bcadd($buffetTotalPrice, $delayTotalPrice), $sendKitchenProductTotalPrice);

            $list = OrderProduct::getGroupByTime($order['order_id'], $buffet, $delay);
            array_multisort(array_column($list,'timestamp'), SORT_DESC, $list); //SORT_DESC降序，SORT_ASC升序
        }
        return $this->renderSuccess('请求成功', compact('list', 'total_price'));
    }

    /**
     * @Apidoc\Title("修改商品数量")
     * @Apidoc\Method("POST")
     * @Apidoc\Url ("/index.php/tablet/order.Order/sub")
     * @Apidoc\Param("order_product_id", type="int", require=true, desc="订单商品ID")
     * @Apidoc\Param("product_num", type="int", require=true, desc="商品数量")
     * @Apidoc\Returned()
     */
    public function sub($order_product_id)
    {
        $model = OrderProduct::detail($order_product_id);
        if ($model->sub($this->postData())) {
            (new OrderModel())->reloadPrice($model['order_id']);
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * @Apidoc\Title("添加商品")
     * @Apidoc\Method("POST")
     * @Apidoc\Url ("/index.php/tablet/order.Order/add")
     * @Apidoc\Param("product_id", type="int", require=true, desc="商品ID")
     * @Apidoc\Param("product_num", type="int", require=true, desc="商品数量")
     * @Apidoc\Param("product_sku_id", type="int", require=true, desc="商品SKU ID")
     * @Apidoc\Param("attr", type="int", require=false, desc="商品属性，如果有必填")
     * @Apidoc\Param("feed", type="string", require=false, desc="加料")
     * @Apidoc\Param("describe", type="string", require=false, desc="描述，拼接商品的规格，属性加料。如：小份;蒜蓉;番茄,茄子;")
     * @Apidoc\Param("price", type="float", require=true, desc="实收商品价格")
     * @Apidoc\Param("product_price", type="float", require=true, desc="商品价格")
     * @Apidoc\Param("bag_price", type="float", require=true, desc="打包费")
     * @Apidoc\Param("table_id", type="int", require=true, desc="桌台ID")
     * @Apidoc\Param("is_buffet", type="int", require=true, desc="是否自助餐 0-否 1-是")
     * @Apidoc\Returned()
     */
    public function add($table_id)
    {
        $detail = OrderModel::getTableUnderwayOrder($table_id);
        if (!$detail) {
            return $this->renderError('桌台已关闭');
        }
        $order_id = $detail['order_id'];
        $data = $this->postData();
        unset($data['table_id']);
        $data['order_id'] = $order_id;
        $data['eat_type'] = 10;
        $model = new OrderModel();
        $order_id = $model->addToOrder($data, $this->table);
        if ($order_id > 0) {
            return $this->renderSuccess('添加商品成功', ['order_id' => $order_id]);
        }
        return $this->renderError($model->getError() ?: '添加商品失败');
    }

    /**
     * @Apidoc\Title("桌台下单(送厨)")
     * @Apidoc\Method("POST")
     * @Apidoc\Url ("/index.php/tablet/order.Order/sendKitchen")
     * @Apidoc\Param("table_id", type="int", require=true, desc="桌台ID")
     * @Apidoc\Returned()
     */
    public function sendKitchen($table_id)
    {
        $detail = OrderModel::getTableUnderwayOrder($table_id);
        if (!$detail) {
            return $this->renderError('订单不存在');
        }
        $order_id = $detail['order_id'];
        $model = new OrderProduct();
        if ($model->sendKitchen($order_id)) {
            return $this->renderSuccess('下单成功');
        }
        return $this->renderError($model->getError() ?: '下单失败', $model->getErrorData());
    }

    /**
     * @Apidoc\Title("删除商品")
     * @Apidoc\Method("POST")
     * @Apidoc\Url ("/index.php/tablet/order.Order/delProduct")
     * @Apidoc\Param("order_product_id", type="int|array", require=true, desc="订单商品ID, 多个传数组: [1,2]")
     * @Apidoc\Returned()
     */
    public function delProduct($order_product_id)
    {
        $model = new OrderProduct();
        if ($model->delProduct($order_product_id)) {
            return $this->renderSuccess('删除成功');
        };
        return $this->renderError($model->getError() ?: '删除失败');
    }

    /**
     * @Apidoc\Title("桌台-自助餐列表")
     * @Apidoc\Method("POST")
     * @Apidoc\Url ("/index.php/tablet/order.Order/buffetList")
     * @Apidoc\Returned()
     */
    public function buffetList()
    {
        $list = Buffet::getList();
        return $this->renderSuccess('',$list);
    }

}