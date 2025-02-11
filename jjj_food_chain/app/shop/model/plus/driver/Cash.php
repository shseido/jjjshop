<?php

namespace app\shop\model\plus\driver;

use app\common\library\easywechat\AppWx;
use app\common\library\easywechat\AppMp;
use app\common\model\app\AppMp as AppMpModel;
use app\common\model\app\AppWx as AppWxModel;
use app\common\model\plus\driver\Setting;
use app\common\service\order\OrderService;
use app\common\library\easywechat\WxPay;
use app\common\model\plus\driver\Cash as CashModel;
use app\shop\model\user\User as UserModel;

/**
 * 配送员提现明细模型
 */
class Cash extends CashModel
{
    /**
     * 获取器：申请时间
     */
    public function getAuditTimeAttr($value)
    {
        return $value > 0 ? date('Y-m-d H:i:s', $value) : 0;
    }

    /**
     * 获取器：打款方式
     */
    public function getPayTypeAttr($value)
    {
        return ['text' => $this->payType[$value], 'value' => $value];
    }

    /**
     * 获取配送员提现列表
     */
    public function getList($user_id = null, $apply_status = -1, $pay_type = -1, $search = '')
    {
        $model = $this;
        // 构建查询规则
        $model = $model->alias('cash')
            ->with(['user'])
            ->field('cash.*, driver.real_name, driver.mobile, user.nickName, user.avatarUrl')
            ->join('user', 'user.user_id = cash.user_id')
            ->join('driver_user driver', 'driver.user_id = cash.user_id')
            ->order(['cash.create_time' => 'desc']);
        // 查询条件
        if ($user_id > 0) {
            $model = $model->where('cash.user_id', '=', $user_id);
        }
        if ($search) {
            $model = $model->like('driver.real_name|driver.mobile', $search);
        }
        if ($apply_status > 0) {
            $model = $model->where('cash.apply_status', '=', $apply_status);
        }
        if ($pay_type > 0) {
            $model = $model->where('cash.pay_type', '=', $pay_type);
        }
        // 获取列表数据
        return $model->paginate(15, false, [
            'query' => \request()->request()
        ]);
    }

    /**
     * 配送员提现审核
     */
    public function submit($param)
    {
        $data = ['apply_status' => $param['apply_status']];
        if ($param['apply_status'] == 30) {
            $data['reject_reason'] = $param['reject_reason'];
        }
        // 更新申请记录
        $data['audit_time'] = time();
        self::update($data, ['id' => $param['id']]);
        // 提现驳回：解冻分销商资金
        if ($param['apply_status'] == 30) {
            User::backFreezeMoney($param['user_id'], $param['money']);
        }
        return true;
    }

    /**
     * 确认已打款
     */
    public function money()
    {
        $this->startTrans();
        try {
            // 更新申请状态
            $data = ['apply_status' => 40, 'audit_time' => time()];
            self::update($data, ['id' => $this['id']]);

            // 更新配送员累积提现佣金
            User::totalMoney($this['user_id'], $this['money']);
            // 记录配送员资金明细
            Capital::add([
                'user_id' => $this['user_id'],
                'flow_type' => 20,
                'money' => -$this['money'],
                'describe' => '申请提现',
            ]);
            // 事务提交
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    /**
     * 配送员提现：微信支付企业付款
     */
    public function wechatPay0()
    {
        // 微信用户信息
        $user = UserModel::detail($this['user_id']);
        // 生成付款订单号
        $orderNO = OrderService::createOrderNo();
        // 付款描述
        $desc = '配送员提现付款';
        // 微信支付api：企业付款到零钱
        $open_id = '';
        if ($user['reg_source'] == 'mp') {
            $app = AppMp::getWxPayApp($user['app_id']);
            $open_id = $user['mpopen_id'];
        } else if ($user['reg_source'] == 'wx') {
            $app = AppWx::getWxPayApp($user['app_id']);
            $open_id = $user['open_id'];
        }

        if ($open_id == '') {
            $this->error = '未找到用户open_id';
            return false;
        }

        $WxPay = new WxPay($app);
        // 请求付款api
        if ($WxPay->transfers($orderNO, $open_id, $this['money'], $desc)) {
            // 确认已打款
            $this->money();
            return true;
        }
        return false;
    }

    /**
     * 商家转账到零钱
     */
    public function wechatPay()
    {
        // 微信用户信息
        $user = UserModel::detail($this['user_id']);
        // 生成付款订单号
        $orderNO = OrderService::createOrderNo();
        // 付款描述
        $desc = '余额提现付款';
        // 微信支付api：企业付款到零钱
        $open_id = '';
        $app_id = '';
        if ($user['reg_source'] == 'mp') {
            $open_id = $user['mpopen_id'];
            $wxConfig = AppMpModel::getAppMpCache($app_id);
            $app_id = $wxConfig['mpapp_id'];
        } else if ($user['reg_source'] == 'wx') {
            $open_id = $user['open_id'];
            $wxConfig = AppWxModel::getAppWxCache($app_id);
            $app_id = $wxConfig['wxapp_id'];
        }

        if ($open_id == '') {
            $this->error = '未找到用户open_id';
            return false;
        }
        $wxPay = new WxPay(null);
        $user_name = $wxPay->getEncrypt($user['real_name'], $user['app_id']);
        $pars = [];
        $pars['appid'] = $app_id;//直连商户的appid
        $pars['out_batch_no'] = 'sjzz' . date('Ymd') . mt_rand(1000, 9999);//商户系统内部的商家批次单号，要求此参数只能由数字、大小写字母组成，在商户系统内部唯一
        $pars['batch_name'] = $desc;//该笔批量转账的名称
        $pars['batch_remark'] = $desc;//转账说明，UTF8编码，最多允许32个字符
        $pars['total_amount'] = intval($this['money'] * 100);//转账总金额 单位为“分”
        $pars['total_num'] = 1;//转账总笔数
        $pars['transfer_detail_list'][0] = [
            'out_detail_no' => 'Dh' . $orderNO,
            'transfer_amount' => $pars['total_amount'],
            'transfer_remark' => $desc,
            'openid' => $open_id,
            'user_name' => $user_name
        ];//转账明细列表
        //获取token
        $res = $wxPay->wechatTrans($pars, $user['app_id']);
        $resArr = json_decode($res, true);
        if (isset($resArr['batch_id'])) {
            $this->save([
                'batch_id' => $resArr['batch_id']
            ]);
            // 确认打款
            $this->money();
            return true;
        } else {
            $this->error = $resArr['message'];
            return false;
        }
    }

    /*
     *统计提现总数量
     */
    public function getDriverOrderTotal()
    {
        return $this->count('id');
    }

    /*
    *统计提现总数量
    */
    public function getDriverApplyTotal()
    {
        return $this->where('apply_status', '=', '10')->count();
    }
}