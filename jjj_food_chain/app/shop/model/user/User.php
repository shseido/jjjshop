<?php

namespace app\shop\model\user;

use app\common\model\user\User as UserModel;
use app\common\enum\user\grade\ChangeTypeEnum;
use app\common\model\user\Grade as GradeModel;
use app\shop\model\user\GradeLog as GradeLogModel;
use app\shop\model\user\PointsLog as PointsLogModel;
use app\shop\model\plus\agent\User as AgentUserModel;
use app\common\enum\user\pointsLog\PointsLogSceneEnum;
use app\shop\model\user\BalanceLog as BalanceLogModel;
use app\common\enum\user\balanceLog\BalanceLogSceneEnum as SceneEnum;

/**
 * 用户模型
 */
class User extends UserModel
{
    /**
     * 获取当前用户总数
     */
    public function getUserTotal($day = null)
    {
        $model = $this;
        if (!is_null($day)) {
            $startTime = strtotime($day);
            $model = $model->where('create_time', '>=', $startTime)
                ->where('create_time', '<', $startTime + 86400);
        }
        return $model->where('is_delete', '=', '0')->count();
    }

    /**
     * 获取用户id
     * @return \think\Collection
     */
    public function getUsers($where = null)
    {
        // 获取用户列表
        return $this->where('is_delete', '=', '0')
            ->where($where)
            ->order(['user_id' => 'asc'])
            ->field(['user_id'])
            ->select();
    }

    /**
     * 获取用户列表
     */
    public static function getList($data)
    {
        $model = new static();
        // 搜索关键词
        if (!empty($data['keyword'])) {
            $keyword = trim($data['keyword']);
            $model = $model->where(function ($query) use ($keyword) {
                $query->like('user_id|mobile|nickName', $keyword);
            });
        }
        // 检索：会员等级
        if (isset($data['grade_id']) && $data['grade_id'] > 0) {
            $model = $model->where('grade_id', '=', (int)$data['grade_id']);
        }
        // 检索：注册时间
        if (!empty($data['reg_date'][0])) {
            $model = $model->where('create_time', 'between', [strtotime($data['reg_date'][0]), strtotime($data['reg_date'][1]) + 86399]);
        }
        // 检索：性别
        if (isset($data['gender']) && $data['gender'] > -1) {
            $model = $model->where('gender', '=', (int)$data['gender']);
        }
        // 获取用户列表
        return $model->with(['grade','card'])->where('is_delete', '=', '0')
            ->order(['create_time' => 'desc'])
            ->hidden(['open_id', 'union_id', 'password'])
            ->paginate($data);
    }

    /**
     * 软删除
     */
    public function setDelete()
    {
        // 判断是否为分销商
        if (AgentUserModel::isAgentUser($this['user_id'])) {
            $this->error = '当前用户为分销商，不可删除';
            return false;
        }
        return $this->transaction(function () {
            // 删除用户推荐关系
            (new AgentUserModel)->onDeleteReferee($this['user_id']);
            // 标记为已删除
            return $this->save(['is_delete' => 1]);
        });
    }

    /**
     * 新增记录
     */
    public function add($data)
    {
        $nickName = $data['nick_name'] ?? null;
        $mobile = $data['mobile'] ?? null;
        $password = $data['password'] ?? null;
        $gender = $data['gender'] ?? null;
        $gradeId = $data['grade_id'] ?? GradeModel::getDefaultGradeId();
        $birthday = $data['birthday'] ?? null;

        if (!$nickName || !$mobile) {
            $this->error = !$nickName ? '昵称不能为空' : '手机号不能为空';
            return false;
        }

        // 最长20位，不限制格式
        if (strlen($mobile) > 20) {
            $this->error = '手机号长度不能超过20位';
            return false;
        }
        // 校验手机号格式
        // if (!checkMobile($mobile)) {
        //     $this->error = '手机号格式不正确';
        //     return false;
        // }

        $user = $this->where('mobile', '=', $mobile)
            ->where('is_delete', '=', 0)
            ->find();

        if ($user) {
            $this->error = '会员已存在';
            return false;
        }

        return $this->save([
            'nickName' => $nickName,
            'mobile' => $mobile,
            'password' => md5($password),
            'reg_source' => 'home', //注册来源
            'gender' => $gender, //性别
            'grade_id' => $gradeId, //默认等级
            'birthday' => $birthday ? strtotime($birthday) : 0, //生日
            'app_id' => self::$app_id,
        ]);
    }

    /**
     * 修改记录
     */
    public function editUser($data)
    {
        if ($data['mobile']) {
            $mobile = $this->where('mobile', '=', $data['mobile'])
                ->where('user_id', '<>', $this['user_id'])
                ->where('reg_source', '=', $this['reg_source'])
                ->where('is_delete', '=', 0)
                ->count();
            if ($mobile) {
                $this->error = "手机号已存在";
                return false;
            }
        }
        if ($data['password'] ?? '') {
            $data['password'] = md5($data['password']);
        } else {
            unset($data['password']);
        }
        if ($data['birthday']) {
            $data['birthday'] = strtotime($data['birthday']);
        }
        $data['nickName'] = isset($data['nick_name']) ? $data['nick_name'] : $this['nickName'];
        return $this->save($data);
    }

    /**
     * 修改用户等级
     */
    public function updateGrade($data)
    {
        if (!isset($data['remark'])) {
            $data['remark'] = '';
        }
        // 变更前的等级id
        $oldGradeId = $this['grade_id'];
        return $this->transaction(function () use ($oldGradeId, $data) {
            // 更新用户的等级
            $status = $this->save(['grade_id' => $data['grade_id']]);
            // 新增用户等级修改记录
            if ($status) {
                (new GradeLogModel)->save([
                    'user_id' => $this['user_id'],
                    'old_grade_id' => $oldGradeId,
                    'new_grade_id' => $data['grade_id'],
                    'change_type' => ChangeTypeEnum::ADMIN_USER,
                    'remark' => $data['remark'],
                    'app_id' => $this['app_id']
                ]);
            }
            return $status !== false;
        });
    }

    /**
     * 消减用户的实际消费金额
     */
    public function setDecUserExpend($userId, $expendMoney)
    {
        return $this->where(['user_id' => $userId])->dec('expend_money', $expendMoney)->update();
    }

    /**
     * 用户充值
     */
    public function recharge($storeUserName, $source, $data)
    {
        if ($source == 0) {
            return $this->rechargeToBalance($storeUserName, $data['balance']);
        } elseif ($source == 1) {
            return $this->rechargeToPoints($storeUserName, $data['points']);
        }
        return false;
    }

    /**
     * 用户充值：余额
     */
    private function rechargeToBalance($storeUserName, $data)
    {
        if (!isset($data['money']) || $data['money'] === '' || $data['money'] < 0) {
            $this->error = '请输入正确的金额';
            return false;
        }
        // 判断是否是正确的数字金额
        if (!is_numeric($data['money'])) {
            $this->error = '请输入正确的金额';
            return false;
        }
        if ($data['money'] > 100000000) {
            $this->error = '不能大于100000000';
            return false;
        }
        // 判断充值方式，计算最终金额
        $money = 0;
        if ($data['mode'] === 'inc') {
            $diffMoney = $this['balance'] + $data['money'];
            $money = $data['money'];
        } elseif ($data['mode'] === 'dec') {
            if ($this['balance'] == 0) {
                $this->error = '余额不能小于0';
                return false;
            }
            if ($this['balance'] - $data['money'] < 0) {
                $this->error = '余额不能小于当前数值';
                return false;
            }
            $diffMoney = $this['balance'] - $data['money'] <= 0 ? 0 : $this['balance'] - $data['money'];
            $money = -$data['money'];
        } else {
            $diffMoney = $data['money'];
            $money = $diffMoney - $this['balance'];
        }
        $maxLimit = 999999999;
        if ($diffMoney > $maxLimit) {
            $this->error = '充值后的余额不能大于' . $maxLimit;
            return false;
        }
        // 更新记录
        $this->transaction(function () use ($storeUserName, $data, $diffMoney, $money) {
            // 更新账户余额
            $this->where('user_id', '=', $this['user_id'])->update(['balance' => $diffMoney]);
            // 新增余额变动记录
            BalanceLogModel::add(SceneEnum::ADMIN, [
                'user_id' => $this['user_id'],
                'card_id' => $this['user_id'],
                'money' => $money,
                'remark' => $data['remark'],
            ], [$storeUserName]);
        });
        return true;
    }

    /**
     * 用户充值：积分
     */
    private function rechargeToPoints($storeUserName, $data)
    {
        if (!isset($data['value']) || $data['value'] === '' || $data['value'] < 0) {
            $this->error = '请输入正确的积分数量';
            return false;
        }
        // 判断是否是正确的数字金额
        if (!is_numeric($data['value'])) {
            $this->error = '请输入正确的金额';
            return false;
        }
        if ($data['value'] > 100000000) {
            $this->error = '不能大于100000000';
            return false;
        }
        $points = 0;
        // 判断充值方式，计算最终积分
        if ($data['mode'] === 'inc') {
            $diffMoney = $this['points'] + $data['value'];
            $points = $data['value'];
        } elseif ($data['mode'] === 'dec') {
            if ($this['points'] == 0) {
                $this->error = '积分不能小于0';
                return false;
            }
            if ($this['points'] - $data['value'] < 0) {
                $this->error = '积分不能小于当前数值';
                return false;
            }
            $diffMoney = $this['points'] - $data['value'] <= 0 ? 0 : $this['points'] - $data['value'];
            $points = -$data['value'];
        } else {
            $diffMoney = $data['value'];
            $points = $data['value'] - $this['points'];
        }
        $maxLimit = 999999999;
        if ($diffMoney > $maxLimit) {
            $this->error = '充值后的余额不能大于' . $maxLimit;
            return false;
        }
        // 更新记录
        $this->transaction(function () use ($storeUserName, $data, $diffMoney, $points) {
            $totalPoints = $this['total_points'] + $points <= 0 ? 0 : $this['total_points'] + $points;
            // 更新账户积分
            $this->where('user_id', '=', $this['user_id'])->update([
                'points' => $diffMoney,
                'total_points' => $totalPoints
            ]);
            // 新增积分变动记录
            PointsLogModel::add([
                'user_id' => $this['user_id'],
                'card_id' => $this['user_id'],
                'scene' => PointsLogSceneEnum::ADMIN,
                'value' => $points,
                'describe' => "后台管理员 [{$storeUserName}] 操作",
                'remark' => $data['remark'],
            ]);
        });
        event('UserGrade', $this['user_id']);
        return true;
    }


    /**
     * 获取用户统计数量
     */
    public function getUserData($startDate, $endDate, $type)
    {
        $model = $this;
        if (!is_null($startDate)) {
            $model = $model->where('create_time', '>=', strtotime($startDate));
        }
        if (is_null($endDate)) {
            $model = $model->where('create_time', '<', strtotime($startDate) + 86400);
        } else {
            $model = $model->where('create_time', '<', strtotime($endDate) + 86400);
        }
        if ($type == 'user_total' || $type == 'user_add') {
            return $model->count();
        } else if ($type == 'user_pay') {
            return $model->where('pay_money', '>', '0')->count();
        } else if ($type == 'user_no_pay') {
            return $model->where('pay_money', '=', '0')->count();
        }
        return 0;
    }

    /**
     * 提现打款成功：累积提现余额
     */
    public static function totalMoney($user_id, $money)
    {
        $model = self::detail($user_id);
        return $model->save([
            'freeze_money' => $model['freeze_money'] - $money,
            'cash_money' => $model['cash_money'] + $money,
        ]);
    }

    /**
     * 提现驳回：解冻用户余额
     */
    public static function backFreezeMoney($user_id, $money)
    {
        $model = self::detail($user_id);
        return $model->save([
            'balance' => $model['balance'] + $money,
            'freeze_money' => $model['freeze_money'] - $money,
        ]);
    }
}
