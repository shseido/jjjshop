<?php

namespace app\shop\controller\setting;

use app\shop\controller\Controller;
use app\shop\model\settings\Setting as SettingModel;
use app\common\enum\settings\SettingEnum;
use hg\apidoc\annotation as Apidoc;

/**
 * 相关设置
 * @Apidoc\Group("supplier")
 * @Apidoc\Sort(5)
 */
class Supplier extends Controller
{
    /**
     * @Apidoc\Title("货币单位(get-获取/post-设置)")
     * @Apidoc\Method ("POST")
     * @Apidoc\Url ("/index.php/shop/setting.Supplier/currencyUnit")
     * @Apidoc\Param("unit", type="string", require=true, default="", desc="主货币单位")
     * @Apidoc\Param("is_open", type="int", require=true, default=0, desc="是否开启副货币单位")
     * @Apidoc\Param("vice_unit", type="string", require=true, default="", desc="副货币单位")
     * @Apidoc\Param("unit_rate", type="string", require=true, default="", desc="主副货币转换比例")
     * @Apidoc\Returned()
     */
    public function currencyUnit()
    {
        if($this->request->isGet()){
            return $this->fetchData(SettingEnum::CURRENCY);
        }
        $model = new SettingModel;
        $data = $this->request->param();
        //
        $unit_rate = $data['unit_rate'] ?? '';
        if (empty($data['unit'])) {
            return $this->renderError('主货币单位不能为空');
        }
        if (!empty($data['is_open']) && (empty($data['vice_unit']) || empty($unit_rate))) {
            return $this->renderError('副货币单位和主副货币汇率不能为空');
        }
        if (!empty($data['is_open']) && !is_numeric($unit_rate)) {
            return $this->renderError('主副货币汇率必须为数字');
        }

        $arr = [
            'unit' => $data['unit'], // 主货币单位
            'is_open' => $data['is_open'], // 是否开启副货币单位
            'vice_unit' => $data['vice_unit'], // 副货币单位
            'unit_rate' => max(0, $unit_rate), // 主副货币转换比例
        ];
        $shop_supplier_id = $this->store['user']['shop_supplier_id'];
        if ($model->edit(SettingEnum::CURRENCY, $arr, $shop_supplier_id)) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * @Apidoc\Title("税率管理(get-获取/post-设置)")
     * @Apidoc\Method ("POST")
     * @Apidoc\Url ("/index.php/shop/setting.Supplier/taxRate")
     * @Apidoc\Param("is_open", type="int", require=true, default=0, desc="是否开启副货币单位")
     * @Apidoc\Param("tax_rate", type="string", require=true, default="", desc="税率")
     * @Apidoc\Returned()
     */
    public function taxRate()
    {
        if($this->request->isGet()){
            return $this->fetchData(SettingEnum::TAX_RATE);
        }
        $model = new SettingModel;
        $data = $this->request->param();
        //
        $tax_rate = $data['tax_rate'] ?? '';
        if (!empty($data['is_open']) && $tax_rate === '') {
            return $this->renderError('税率不能为空');
        }
        if (!empty($data['is_open']) && !is_numeric($tax_rate)) {
            return $this->renderError('税率必须为数字');
        }
        if ($tax_rate > 100) {
            return $this->renderError('税率最大值只能为100');
        }
        $arr = [
            'is_open' => $data['is_open'], // 是否开启税率
            'tax_rate' => max(0, $tax_rate), // 税率
        ];
        $shop_supplier_id = $this->store['user']['shop_supplier_id'];
        if ($model->edit(SettingEnum::TAX_RATE, $arr, $shop_supplier_id)) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * @Apidoc\Title("服务费(get-获取/post-设置)")
     * @Apidoc\Method ("POST")
     * @Apidoc\Url ("/index.php/shop/setting.Supplier/serviceCharge")
     * @Apidoc\Param("is_open", type="int", require=true, default=0, desc="是否开启副货币单位")
     * @Apidoc\Param("service_charge", type="string", require=true, default="", desc="服务费")
     * @Apidoc\Returned()
     */
    public function serviceCharge()
    {
        if($this->request->isGet()){
            return $this->fetchData(SettingEnum::SERVICE_CHARGE);
        }
        $model = new SettingModel;
        $data = $this->request->param();
        //
        $server_charge = $data['service_charge'] ?? '';
        if (!empty($data['is_open']) && $server_charge === '') {
            return $this->renderError('服务费不能为空');
        }
        if (!empty($data['is_open']) && !is_numeric($server_charge)) {
            return $this->renderError('服务费必须为数字');
        }
        $arr = [
            'is_open' => $data['is_open'], // 是否开启服务费
            'service_charge' => max(0, $server_charge), // 服务费
        ];
        $shop_supplier_id = $this->store['user']['shop_supplier_id'];
        if ($model->edit(SettingEnum::SERVICE_CHARGE, $arr, $shop_supplier_id)) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 获取配置值
     */
    public function fetchData($key)
    {
        if ($key == '') {
            return $this->renderError('缺少参数');
        }
        $vars['values'] = SettingModel::getSupplierItem($key, $this->store['user']['shop_supplier_id']);
        return $this->renderSuccess('', compact('vars'));
    }
}
