<?php

namespace app\shop\controller\user;

use app\common\enum\settings\SettingEnum;
use app\shop\controller\Controller;
use app\shop\model\settings\Setting as SettingModel;
use app\shop\model\user\BalanceLog as BalanceLogModel;
use hg\apidoc\annotation as Apidoc;

/**
 * 余额明细
 * @Apidoc\Group("user")
 * @Apidoc\Sort(5)
 */
class Balance extends Controller
{
    /**
     * @Apidoc\Title("余额明细")
     * @Apidoc\Method ("POST")
     * @Apidoc\Url ("/index.php/shop/user.balance/log")
     * @Apidoc\Param("keyword", type="string", require=false, default="", desc="用户昵称")
     * @Apidoc\Param("scene", type="int", require=false, default="", desc="变动场景")
     * @Apidoc\Param("date", type="array", require=false, default="", desc="起始日期")
     * @Apidoc\Param(ref="pageParam")
     * @Apidoc\Returned("list", type="array", ref="app\shop\model\user\BalanceLog\getList", desc="列表")
     */
    public function log()
    {
        $model = new BalanceLogModel;
        return $this->renderSuccess('', [
            // 充值记录列表
            'list' => $model->getList($this->postData()),
            // 属性集
            'attributes' => $model::getAttributes(),
        ]);
    }

    /**
     * @Apidoc\Title("余额充值设置")
     * @Apidoc\Method ("POST")
     * @Apidoc\Url ("/index.php/shop/user.balance/setting")
     * @Apidoc\Param("is_open", type="int", require=true, default="", desc="是否开启余额充值 0-关闭 1-开启")
     * @Apidoc\Param("is_plan", type="int", require=true, default="", desc="是否开启自定义金额 0-关闭 1-开启")
     * @Apidoc\Param("min_money", type="float", require=true, default="", desc="最低充值金额")
     * @Apidoc\Param("describe", type="string", require=true, default="", desc="充值说明")
     * @Apidoc\Returned()
     */
    public function setting()
    {
        if ($this->request->isGet()) {
            $values = SettingModel::getItem(SettingEnum::BALANCE);
            return $this->renderSuccess('', compact('values'));
        }
        $model = new SettingModel;
        if ($model->edit(SettingEnum::BALANCE, $this->postData())) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }
}