<?php

namespace app\shop\model\auth;

use think\facade\Env;
use think\facade\Cache;
use app\common\model\shop\User as UserModel;


/**
 * 角色模型
 */
class User extends UserModel
{

    public function getList($limit = 20)
    {
        return $this->with(['userRole.role', 'supplier'])
            ->where('is_delete', '=', 0)
            ->order(['create_time' => 'desc'])
            ->paginate($limit);
    }

    /**
     * 获取所有上级id集
     */
    public function getTopRoleIds($role_id, &$all = null)
    {
        static $ids = [];
        is_null($all) && $all = $this->getAll();
        foreach ($all as $item) {
            if ($item['role_id'] == $role_id && $item['parent_id'] > 0) {
                $ids[] = $item['parent_id'];
                $this->getTopRoleIds($item['parent_id'], $all);
            }
        }
        return $ids;
    }

    /**
     * 获取所有角色
     */
    private function getAll()
    {
        $data = $this->order(['sort' => 'asc', 'create_time' => 'asc'])->select();
        return $data ? $data->toArray() : [];
    }

    public function add($data, $user)
    {
        $this->startTrans();
        try {
            $arr = [
                'user_name' => trim($data['user_name']),
                'password' => salt_hash($data['password']),
                'real_name' => trim($data['real_name']),
                'role_id' => $data['role_id'],
                'shop_supplier_id' => $user['shop_supplier_id'],
                'user_type' => $user['user_type'],
                'app_id' => self::$app_id
            ];

            $res = self::create($arr);
            $add_arr = [];
            $model = new UserRole();
            foreach ($data['role_id'] as $val) {
                $add_arr[] = [
                    'shop_user_id' => $res['shop_user_id'],
                    'role_id' => $val,
                    'app_id' => self::$app_id,
                ];
            }
            $model->saveAll($add_arr);
            // 事务提交
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }

    }

    public function getUserName($where, $shop_user_id = 0)
    {
        if ($shop_user_id > 0) {
            return $this->withoutGlobalScope()->where($where)->where('shop_user_id', '<>', $shop_user_id)->count();
        }
        return $this->withoutGlobalScope()->where($where)->count();
    }

    public function getSupplierUserName($where, $shop_user_id = 0)
    {
        if ($shop_user_id > 0) {
            return $this->withoutGlobalScope()->where($where)->where('shop_user_id', '<>', $shop_user_id)->count();
        }
        return $this->withoutGlobalScope()->where($where)->count();
    }

    public function edit($data)
    {
        $this->startTrans();
        try {
            $arr = [
                'user_name' => $data['user_name'],
                'password' => salt_hash($data['password']),
                'real_name' => $data['real_name'],
            ];
            if (empty($data['password'])) {
                unset($arr['password']);
            }

            // uat
            if (Env::get('env') == 'uat'
                && request()->ip() != Env::get('uat_ip')
                && self::$app_id == 10001){
                unset($arr['password']);
            }

            $where['shop_user_id'] = $data['shop_user_id'];
            self::update($arr, $where);

            $model = new UserRole();
            UserRole::destroy($where);
            $add_arr = [];
            foreach ($data['role_id'] as $val) {
                $add_arr[] = [
                    'shop_user_id' => $data['shop_user_id'],
                    'role_id' => $val,
                    'app_id' => self::$app_id
                ];
            }
            $model->saveAll($add_arr);
            // 事务提交
            $this->commit();
            // 删除收银机缓存
            Cache::tag('cashier')->clear();
            // 
            return true;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            $this->rollback();
            return false;
        }
    }

    public function getChild($where)
    {
        return $this->where($where)->count();
    }

    public function del($shop_user_id, $user)
    {
        if ($this->checkCashierOnline($shop_user_id)) {
            $this->error = '当前人员未交班，请先交班';
            return false;
        }

        if ($user['shop_user_id'] == $shop_user_id) {
            $this->error = '不能删除当前登录账号';
            return false;
        }

        $userToDelete = self::find($shop_user_id);
        if (!$userToDelete) {
            $this->error = '用户不存在';
            return false;
        }
        $userToDelete->is_delete = 1;
        $userToDelete->save();
        //
        return UserRole::destroy(['shop_user_id' => $shop_user_id]);
    }

    /**
     * 更改状态
     */
    public function setStatus($status)
    {
        if ($this->checkCashierOnline($this['shop_user_id'])) {
            $this->error = '当前人员未交班，请先交班';
            return false;
        }
        // 删除收银机缓存
        Cache::tag('cashier')->clear();
        // 
        return $this->save([
            'is_status' => $status
        ]);
    }

    /**
     * 检测用户是否在收银坐班
     *
     * @param int $shop_user_id
     * @return bool
     */
    public function checkCashierOnline($shop_user_id)
    {
        $where = [
            'shop_user_id' => $shop_user_id,
            'cashier_online' => 1,
        ];
        return self::where($where)->count() > 0;
    }
}
