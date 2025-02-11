<?php

namespace app\tablet\model\product;

use app\common\model\supplier\Supplier;
use app\common\model\product\Product as ProductModel;

/**
 * 商品模型
 */
class Product extends ProductModel
{
    /**
     * 获取商品列表
     */
    public function list($params)
    {
        // 筛选条件
        $model = $this;
        if (isset($params['is_special']) && $params['is_special'] > 0) {
            $model = $model->where('product.special_id', '=', $params['category_id']);
        } else if (isset($params['category_id']) && $params['category_id'] > 0) {
            $categoryIds = Category::where('category_id', '=', $params['category_id'])
                ->whereOr('parent_id', '=', $params['category_id'])
                ->column('category_id');
            $model = $model->whereIn('product.category_id', $categoryIds);
        } else {
            // 全部
            $categoryIds = Category::where('status', '=', 1)->column('category_id');
            $model = $model->whereIn('product.category_id', $categoryIds);
        }
        if (isset($params['search']) && $params['search'] != '') {
            $model = $model->like('product_name', trim($params['search']));
        }

        // 平板售罄开关开关
        $settingInfo = Supplier::getTabletBaseInfo();
        if (!(isset($settingInfo['tablet']) && $settingInfo['tablet']['is_show_sold_out'])) {
            $model = $model->where('product_stock', '>', 0 );
        }

        if (isset($params['order_id'])) {
            $order_id = $params['order_id'];
            $model = $model->alias('product')
                ->field(['product.*'])
                ->with(['category', 'image.file', 'sku', 'orderProducts' => function ($query) use ($order_id) {
                    $query->where('order_id', $order_id);
                }]);
        } else {
            $model = $model->alias('product')
                ->field(['product.*'])
                ->with(['category', 'image.file', 'sku']);
        }

        return $model->where('product.is_delete', '=', 0)
            ->where('product.is_show_tablet', '=', 1)
            ->where('product.product_type', '=', 1)
            ->where('product.shop_supplier_id', '=', $params['shop_supplier_id'])
            ->where('product_status', '=', 10)
            ->order(['product_sort', 'product_id' => 'desc'])->paginate($params)->toArray();
    }

}
