<?php

namespace app\shop\model\shop;

use app\common\model\shop\LoginLog as LoginLogModel;
use app\common\model\shop\User as UserModel;
use app\common\model\shop\Access as AccessModel;
use app\shop\model\settings\Setting as SettingModel;

/**
 * 后台管理员登录模型
 */
class User extends UserModel
{
    /**
     *检查登录
     */
    public function checkLogin($user)
    {
        $where['user_name'] = $user['username'];
        $where['password'] = $user['password'];
        if (!$user = $this->where($where)->with(['app', 'supplier'])->find()) {
            $this->error = '账号或密码错误';
            return false;
        }
        if ($user['is_delete'] == 1) {
            $this->error = '账号被删除，请联系管理员';
            return false;
        }
        if ($user['is_status'] == 1) {
            $this->error = '账号被禁用，请联系管理员';
            return false;
        }
        if (empty($user['app'])) {
            $this->error = '登录失败, 未找到应用信息';
            return false;
        }
        if ($user['app']['is_delete']) {
            $this->error = '登录失败, 当前应用已删除';
            return false;
        }
        if ($user['app']['is_recycle']) {
            $this->error = '登录失败, 当前应用已禁用';
            return false;
        }
        if ($user['app']['expire_time'] != 0 && $user['app']['expire_time'] < time()) {
            $this->error = '登录失败, 当前应用已过期，请联系平台续费';
            return false;
        }
        // 验证权限
        $permission = (new AccessModel)->getPermission(AccessModel::SHOP_ROUTE_NAME, $user, $user['supplier']);
        if (empty($permission)) {
            $this->error = '当前无权限，请联系管理员';
            return false;
        }
        // 保存登录状态
        $user['token'] = signToken($user['shop_user_id'], 'shop');
        // 货币信息
        $user['currency'] = SettingModel::getCurrency($user['shop_supplier_id'], $user['app_id']);
        // 写入登录日志
        LoginLogModel::add($where['user_name'], \request()->ip(), '登录成功', $user['app']['app_id']);
        return $user;
    }


    /*
    * 修改密码
    */
    public function editPass($data, $user)
    {
        // 校验密码为数字
        if (!validateNumber($data['oldpass']) || !validateNumber($data['password']) || !validateNumber($data['confirmPass'])){
            $this->error = '密码为4-16位纯数字';
            return false;
        }
        $user_info = User::detail($user['shop_user_id']);
        if ($user_info['password'] != salt_hash($data['oldpass'])) {
            $this->error = '原密码错误';
            return false;
        }
        if ($data['password'] != $data['confirmPass']) {
            $this->error = '两次密码输入不一致';
            return false;
        }
        $date['password'] = salt_hash($data['password']);
        $user_info->save($date);
        return true;
    }

    /**
     * 获取用户信息
     */
    public static function getUser($data)
    {
        return (new static())->where('shop_user_id', '=', $data['uid'])->with(['app'])->find();
    }

}