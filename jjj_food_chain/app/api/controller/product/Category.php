<?php

namespace app\api\controller\product;

use app\api\model\product\Category as CategoryModel;
use app\api\controller\Controller;
use app\api\model\product\Product as ProductModel;
use app\api\model\supplier\Supplier as SupplierModel;
use app\api\model\shop\FullReduce as FullReduceModel;

/**
 * 商品分类控制器
 */
class Category extends Controller
{
    /**
     * 分类页面
     */
    public function index($type)
    {
        // 整理请求的参数
        $param = $this->postData();
        if ($param['shop_supplier_id'] == 0) {//未选择门店传默认门店
            //获取默认门店id
            $supplier = (new SupplierModel)->getDefault($this->postData());
            $param['shop_supplier_id'] = $supplier['shop_supplier_id'];
        }
        //普通分类
        $commonList = CategoryModel::getApiALL($type, 0, $param['shop_supplier_id']);
        //特殊分类
        $specialList = CategoryModel::getApiALL($type, 1, $param['shop_supplier_id']);
        $param['type'] = 'sell';
        // 获取列表数据
        $model = new ProductModel;
        foreach ($commonList as &$category) {
            $param['category_id'] = $category['category_id'];
            $category['products'] = $model->getList($param, $this->getUser(false));
        }
        foreach ($specialList as &$category) {
            $param['special_id'] = $category['category_id'];
            $param['category_id'] = 0;
            $category['products'] = $model->getList($param, $this->getUser(false));
        }
        $list = array_merge($specialList, $commonList);
        $SupplierModel = new SupplierModel;
        //门店信息
        $supplier = $SupplierModel->getDetail($param);
        //计算距离
        if ($param['delivery'] == 10 && $type == 0) {
            $supplier['distance'] = $SupplierModel->calculateDistance($supplier, $this->getUser(false));
        }
        //当前地址id
        $address_id = $this->getUser(false) ? $this->getUser()['address_id'] : 0;
        //获取满减数据
        $reduceList = (new FullReduceModel)->getList($param['shop_supplier_id']);
        return $this->renderSuccess('', compact('list', 'supplier', 'address_id', 'reduceList'));
    }

}