<?php

namespace app\common\model\user;

use app\common\model\BaseModel;

/**
 * 用户等级模型
 */
class Grade extends BaseModel
{
    protected $pk = 'grade_id';
    protected $name = 'user_grade';

    /**
     * 用户等级模型初始化
     */
    public static function init()
    {
        parent::init();
    }

    /**
     * 备注信息翻译
     */
    public function getRemarkAttr($value, $data)
    {
        if ($data['is_default'] == 1) {
            return __($value);
        }
        // 
        $remark = '';
        if($data['open_money'] == 1){
            $money = sprintf('%.2f',$data['upgrade_money']);
            $remark .= __("会员消费满") . " {$money} " . __("可升级到此等级");
        }
        if($data['open_points'] == 1){
            if(!empty($remark)){
                $remark .= '\r\n';
            }
            $remark .= __("会员积分满") . " {$data['upgrade_points']} " . __("可升级到此等级");
        }
        if($data['open_invite'] == 1){
            if(!empty($remark)){
                $remark .= '\r\n';
            }
            $remark .= __("会员邀请人数满") . " {$data['upgrade_invite']} " . __("可升级到此等级");
        }
        return $remark;
    }

    /**
     * 获取详情
     */
    public static function detail($grade_id)
    {
        return self::find($grade_id);
    }

    /**
     * 获取列表记录
     */
    public function getLists()
    {
        return $this->where('is_delete', '=', 0)
            ->field('grade_id,name')
            ->order(['weight' => 'asc', 'create_time' => 'asc'])
            ->select();
    }

    /**
     * 获取可用的会员等级列表
     */
    public static function getUsableList($appId = null)
    {
        $model = new static;
        $appId = $appId ? $appId : $model::$app_id;
        return $model->where('is_delete', '=', '0')
            ->where('app_id', '=', $appId)
            ->order(['weight' => 'asc', 'create_time' => 'asc'])
            ->select();
    }

    /**
     * 获取可用的会员等级列表(升级使用)
     */
    public static function getUsable($appId = null)
    {
        $model = new static;
        $appId = $appId ? $appId : $model::$app_id;
        return $model->where('is_delete', '=', '0')
            ->where('app_id', '=', $appId)
            ->order(['weight' => 'desc'])
            ->select();
    }

    /**
     * 获取默认等级id
     */
    public static function getDefaultGradeId(){
        $grade = self::where('is_default', '=', 1)->find();
        return $grade['grade_id'];
    }
}