<?php

namespace app\common\model\product;

use app\common\model\BaseModel;
use app\common\model\order\OrderProduct;
use app\shop\model\product\Category as CategoryModel;

/**
 * 商品模型
 */
class Product extends BaseModel
{
    protected $name = 'product';
    protected $pk = 'product_id';
    protected $append = ['product_sales', 'product_name_text', 'product_unit_text'];

    /**
     * 获取商品数据
     */
    public function getProductNameTextAttr($value, $data)
    {
        return extractLanguage($data['product_name'] ?? '');
    }

    /**
     * 获取单位
     */
    public function getProductUnitTextAttr($value, $data)
    {
        return extractLanguage($data['product_unit'] ?? '');
    }

    /**
     * 属性
     */
    public function getProductAttrAttr($value)
    {
        $datas = $value ? json_decode($value, true) : [];
        foreach($datas as $key=>$data){
            $datas[$key]['attribute_name_text'] = extractLanguage($datas[$key]['attribute_name']);
            if(isset($datas[$key]['attribute_value'])){
                foreach($datas[$key]['attribute_value'] as $k=>$v){
                    $datas[$key]['attribute_value_text'][$k] = extractLanguage($v);
                }
            }
        }
        return $datas;
    }

    /**
     * 饲料
     */
    public function getProductFeedAttr($value)
    {
        $datas = $value ? json_decode($value, true) : [];
        foreach($datas as $key=>$data){
            $datas[$key]['feed_name_text'] = extractLanguage($datas[$key]['feed_name']);
        }
        return $datas;
    }

    /**
     * 计算显示销量 (初始销量 + 实际销量)
     */
    public function getProductSalesAttr($value, $data)
    {
        return $data['sales_initial'] + $data['sales_actual'];
    }

    /**
     * 属性配置
     */
    public function setProductAttrAttr($value)
    {
        return $value ? json_encode($value) : '';
    }

    /**
     * 属性加料
     */
    public function setProductFeedAttr($value)
    {
        return $value ? json_encode($value) : '';
    }

    /**
     * 获取器：单独设置折扣的配置
     */
    public function getAloneGradeEquityAttr($value)
    {
        return $value ? json_decode($value, true) : '';
    }

    /**
     * 修改器：单独设置折扣的配置
     */
    public function setAloneGradeEquityAttr($value)
    {
        return json_encode($value);
    }

    /**
     * 关联商品分类表
     */
    public function category()
    {
        return $this->belongsTo('app\\common\\model\\product\\Category');
    }

    /**
     * 关联商品规格表
     */
    public function sku()
    {
        return $this->hasMany('ProductSku')->order(['product_sku_id' => 'asc']);
    }

    /**
     * 关联商品图片表
     */
    public function image()
    {
        return $this->hasMany('app\\common\\model\\product\\ProductImage')->order(['id' => 'asc']);
    }

    /**
     * 关联商品图片表
     */
    public function logo()
    {
        return $this->hasOne('app\\common\\model\\product\\ProductImage')->order(['id' => 'asc']);
    }

    /**
     * 关联供应商表
     */
    public function supplier()
    {
        return $this->belongsTo('app\\common\\model\\supplier\\Supplier', 'shop_supplier_id', 'shop_supplier_id')
            ->field(['shop_supplier_id', 'name', 'address', 'logo']);
    }

    /**
     * 关联订单商品
     */
    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class, 'product_id', 'product_id');
    }

    /**
     * 商品状态
     */
    public function getProductStatusAttr($value, $data)
    {
        $status = [10 => __('上架'), 20 => __('下架')];
        return ['text' => $status[$value], 'value' => $value];
    }

    /**
     * 获取商品列表
     */
    public function getList($param, $page = 0)
    {
        // 商品列表获取条件
        $params = array_merge([
            'type' => 'sell',         // 商品状态
            'category_id' => 0,     // 分类id
            'sortType' => 'all',    // 排序类型
            'list_rows' => 15,       // 每页数量
            'special_id' => 0,        //特殊分类id
            'shop_supplier_id' => 0,
        ], $param);

        // 筛选条件
        $filter = [];
        $model = $this;
        if (isset($params['product_type'])) {
            $model = $model->where('product.product_type', '=', $params['product_type']);
        }
        if (($params['product_ids'] ?? '') != '') {
            $model = $model->whereIn('product.product_id', explode(',', $params['product_ids']));
        }
        if ($params['category_id'] > 0) {
            $categoryIds =(new CategoryModel)
                ->where('category_id', $params['category_id'])
                ->whereOr('parent_id', $params['category_id'])
                ->column('category_id');
            $model = $model->whereIn('category_id', $categoryIds);
        }
        if ($params['special_id'] > 0) {
            $model = $model->where('product.special_id', '=', $params['special_id']);
        }
        if (isset($params['product_name']) && $params['product_name'] != '') {
            $model = $model->like('product_name', $params['product_name']);
        }
        if (!empty($params['search'])) {
            $model = $model->where('product_name', $params['search']);
        }
        // 排序规则
        $sort = [];
        if ($params['sortType'] === 'all') {
            $sort = ['product_sort', 'product_id' => 'desc'];
        }
        if (isset($params['type'])) {
            //出售中
            if ($params['type'] == 'sell') {
                $model = $model->where('product_status', '=', 10);
            }
            //下架
            if ($params['type'] == 'lower') {
                $model = $model->where('product_status', '=', 20);
            }
            //库存紧张
            if ($params['type'] == 'stock') {
                $model = $model->whereBetween('product_stock', [1, 20]);
                $model = $model->where('product_status', '=', 10);
            }
            //已售罄
            if ($params['type'] == 'over') {
                $model = $model->where('product_stock', '=', 0);
                $model = $model->where('product_status', '=', 10);
            }
        }
        // 产品需求，如果sku规格库中数量少于传输的库存数量，就要显示
        if (isset($params['stock']) && $params['stock'] > 0) {
            $stock = (int)$params['stock'];
            $model = $model->join('product_sku sku', 'sku.product_id = product.product_id', 'left')->where('sku.stock_num', '<', $stock)->group('product.product_id');
        }
        //
        if (isset($params['shop_supplier_id']) && $params['shop_supplier_id']) {
            $model = $model->where('product.shop_supplier_id', '=', $params['shop_supplier_id']);
        }
        if (isset($params['product_id']) && $params['product_id']) {
            $model = $model->whereNotIn('product_id', $params['product_id']);
        }
        // 执行查询
        $model = $model->alias('product')
            ->field(['product.*', '(sales_initial + sales_actual) as product_sales'])
            ->with(['category', 'image.file', 'sku', 'supplier'])
            ->where('product.is_delete', '=', 0)
            ->where($filter)
            ->order($sort);

        if ($page == 1) {
            $list = $model->select();
        } else {
            $list = $model->paginate($params);
        }
        // 整理列表数据并返回
        return $this->setProductListData($list, true);
    }

    /**
     * 获取商品列表
     */
    public function getLists($param)
    {
        // 商品列表获取条件
        $params = array_merge([
            'product_status' => 10,         // 商品状态
            'category_id' => 0,     // 分类id
        ], $param);
        // 筛选条件
        $model = $this;
        if ($params['category_id'] > 0) {
            $arr = Category::getSubCategoryId($params['category_id']);
            $model = $model->where('category_id', 'IN', $arr);
        }
        if (isset($params['product_name']) && $params['product_name'] != '') {
            $model = $model->like('product_name', trim($params['product_name']));
        }
        if (isset($params['search']) && $params['search'] != '') {
            $model = $model->like('product_name', trim($params['search']));
        }
        $list = $model
            ->with(['category', 'image.file'])
            ->where('is_delete', '=', 0)
            ->where('product_status', '=', $params['product_status'])
            ->paginate($params);
        // 整理列表数据并返回
        return $this->setProductListData($list, true);
    }

    /**
     * 设置商品展示的数据
     */
    protected function setProductListData($data, $isMultiple = true, callable $callback = null)
    {
        if (!$isMultiple) $dataSource = [&$data]; else $dataSource = &$data;
        // 整理商品列表数据
        foreach ($dataSource as &$product) {
            // 商品主图
            $product['product_image'] = $product['image'] ? $product['image'][0]['file_path'] : '';
            // 商品默认规格
            $product['product_sku'] = self::getShowSku($product);
            // 回调函数
            is_callable($callback) && call_user_func($callback, $product);
        }
        return $data;
    }

    /**
     * 根据商品id集获取商品列表
     */
    public function getListByIds($productIds, $status = null)
    {
        $model = $this;
        $filter = [];
        // 筛选条件
        $status > 0 && $filter['product_status'] = $status;
        if (!empty($productIds)) {
            $model = $model->orderRaw('field(product_id, ' . implode(',', $productIds) . ')');
        }
        // 获取商品列表数据
        $data = $model->with(['category', 'image.file', 'sku'])
            ->where($filter)
            ->where('product_id', 'in', $productIds)
            ->select();

        // 整理列表数据并返回
        return $this->setProductListData($data, true);
    }

    /**
     * 商品多规格信息
     */
    public function getManySpecData($specRel, $skuData)
    {
        // spec_attr
        $specAttrData = [];
        foreach ($specRel as $item) {
            if (!isset($specAttrData[$item['spec_id']])) {
                $specAttrData[$item['spec_id']] = [
                    'group_id' => $item['spec']['spec_id'],
                    'group_name' => $item['spec']['spec_name'],
                    'spec_items' => [],
                ];
            }
            $specAttrData[$item['spec_id']]['spec_items'][] = [
                'item_id' => $item['spec_value_id'],
                'spec_value' => $item['spec_value'],
            ];
        }
        // spec_list
        $specListData = [];
        foreach ($skuData as $item) {
            $image = (isset($item['image']) && !empty($item['image'])) ? $item['image'] : ['file_id' => 0, 'file_path' => ''];
            $specListData[] = [
                'product_sku_id' => $item['product_sku_id'],
                'spec_sku_id' => $item['spec_sku_id'],
                'rows' => [],
                'spec_form' => [
                    'image_id' => $image['file_id'],
                    'image_path' => $image['file_path'],
                    'product_no' => $item['product_no'],
                    'product_price' => $item['product_price'],
                    'product_weight' => $item['product_weight'],
                    'line_price' => $item['line_price'],
                    'stock_num' => $item['stock_num'],
                    'supplier_price' => $item['supplier_price'],
                ],
            ];
        }
        return ['spec_attr' => array_values($specAttrData), 'spec_list' => $specListData];
    }

    /**
     * 获取商品详情
     */
    public static function detail($product_id)
    {
        $model = (new static())->with([
            'category',
            'image.file',
            'sku',
            'supplier'
        ])->where('product_id', '=', $product_id)
            ->find();
        if (empty($model)) {
            return $model;
        }
        // 整理商品数据并返回
        return $model->setProductListData($model, false);
    }

    /**
     * 显示的sku，目前取最低价
     */
    public static function getShowSku($product)
    {
        $result = [];

        //如果是单规格
        if ($product['spec_type'] == 10) {
            $result[] = $product['sku'][0];
        } else {
            //多规格返回最低价
            foreach ($product['sku'] as $sku) {
                if ($product['product_price'] == $sku['product_price']) {
                    $result[] = $sku;
                }
            }
        }

        // 兼容历史数据，如果找不到返回第一个
        if (empty($result) && !empty($product['sku'][0] ?? '')) {
            $result[] = $product['sku'][0];
        }

        return $result;
    }

    /**
     * 获取当前商品总数
     */
    public function getProductTotal($where = [])
    {
        return $this->where('is_delete', '=', 0)->where($where)->count();
    }

    /**
     * 获取当前商品总数
     */
    public function getSupplierProductTotal($shop_supplier_id, $product_type, $product_status = 0)
    {
        $model = $this;
        if ($shop_supplier_id > 0) {
            $model = $model->where('shop_supplier_id', '=', $shop_supplier_id);
        }
        if ($product_type >= 0) {
            $model = $model->where('product_type', '=', $product_type);
        }
        if ($product_status > 0) {
            $model = $model->where('product_status', '=', $product_status);
        }
        return $model->where('is_delete', '=', 0)->count();
    }

    /**
     * 获取店铺销量
     */
    public static function getProductSales($shop_supplier_id)
    {
        $model = new static();
        $sales_initial = $model->where('shop_supplier_id', '=', $shop_supplier_id)
            ->sum('sales_initial');
        $sales_actual = $model->where('shop_supplier_id', '=', $shop_supplier_id)
            ->sum('sales_actual');
        return $sales_initial + $sales_actual;
    }

    /**
     * 获取商品销量Top10
     */
    public function getProductRank($data, $type = 0)
    {
        $model = new OrderProduct;
        if ($data['sort'] == 1) {
            $order = 'total_num desc';
        } else {
            $order = 'total_price desc';
        }
        if ($data['product_type'] >= 0) {
            $model = $model->where('p.product_type', '=', $data['product_type']);
        }
        if ($data['shop_supplier_id']) {
            $model = $model->where('p.shop_supplier_id', '=', $data['shop_supplier_id']);
        }
        switch ($data['type']) {
            case '1'://今天
                $model = $model->where('o.create_time', '>=', strtotime(date('Y-m-d')));
                break;
            case '2'://近7天
                $model = $model->where('o.create_time', '>=', strtotime(-6 . ' days', strtotime(date('Y-m-d'))));
                break;
            case '3'://近15天
                $model = $model->where('o.create_time', '>=', strtotime(-14 . ' days', strtotime(date('Y-m-d'))));
                break;
            case '4'://自定义
                $start = strtotime($data['time'][0]);
                $end = strtotime($data['time'][1]) + 86399;
                $model = $model->where('o.create_time', 'between', "$start,$end");
                break;
            default:
                $model = $model->where('o.create_time', '=', strtotime(date('Y-m-d')));
                break;
        }
        $model = $model->alias('op')
            ->where('o.pay_status', '=', 20)
            ->where('o.order_status', '<>', 20)
            ->join('order o', 'op.order_id=o.order_id')
            ->join('product p', 'p.product_id=op.product_id')
            ->field('p.product_name,p.product_price,sum(op.total_price) as total_price,sum(total_num) as total_num')
            ->group('op.product_id')
            ->order($order);
        if ($type) {
            $list = $model->select();
        } else {
            $list = $model->paginate($data);
        }
        return $list;
    }


    //判断商品每单限购
    public static function getProductLimitNum($product_id)
    {
        return (new self)->where('product_id', '=', $product_id)
            ->where('product_status', '=', 10)
            ->where('is_delete', '=', 0)
            ->value('limit_num');
    }
}
