<?php

namespace app\shop\model\user;

use app\common\model\user\BalanceLog as BalanceLogModel;

/**
 * 用户余额变动明细模型
 */
class BalanceLog extends BalanceLogModel
{
    /**
     * 获取余额变动明细列表
     */
    public function getList($query = [])
    {
        // 设置默认的检索数据
        $params = [
            'user_id' => 0,
            'search' => '',
            'scene' => -1,
            'start_time' => '',
            'end_time' => '',
        ];
        // 合并查询条件
        $data = array_merge($params, $query);
        $model = $this->alias('log')->field('log.*');
        // 搜索关键词
        if (!empty($query['keyword'])) {
            $keyword = trim($query['keyword']);
            $model = $model->where(function ($query) use ($keyword) {
                $query->like('user.user_id|user.mobile|user.nickName', $keyword);
            });
        }
        // 搜索时间段
        if (isset($data['date']) && $data['date'] != '') {
            $model = $model->where('log.create_time', 'between', [strtotime($data['date'][0]), strtotime($data['date'][1]) + 86399]);
        }
        // 余额变动场景
        if (!empty($data['scene']) && $data['scene'] > -1) {
            $model = $model->where('log.scene', '=', (int)$data['scene']);
        }
        // 用户ID
        if (!empty($data['user_id']) && $data['user_id'] > 0) {
            $model = $model->where('log.user_id', '=', (int)$data['user_id']);
        }
        // 获取列表数据
        return $model->with(['user'])
            ->join('user', 'user.user_id = log.user_id')
            ->order(['log.create_time' => 'desc'])
            ->paginate($query, false, [
                'query' => \request()->request()
            ]);
    }

    /**
     * 设置查询条件
     */
    private function setQueryWhere($query)
    {
        if (!empty($query['date'])) {
            $query['start_time'] = $query['date'][0];
            $query['end_time'] = $query['date'][1];
        }
        // 设置默认的检索数据
        $params = $this->setQueryDefaultValue($query, [
            'user_id' => 0,
            'search' => '',
            'scene' => -1,
            'start_time' => '',
            'end_time' => '',
        ]);

        // 用户ID
        $params['user_id'] > 0 && $this->where('log.user_id', '=', $params['user_id']);
        // 用户昵称
        !empty($params['search']) && $this->like('user.nickName', $params['search']);
        // 余额变动场景
        $params['scene'] > -1 && $this->where('log.scene', '=', (int)$params['scene']);
        // 起始时间
        !empty($params['start_time']) && $this->where('log.create_time', '>=', strtotime($params['start_time']));
        // 截止时间
        !empty($params['end_time']) && $this->where('log.create_time', '<', strtotime($params['end_time']) + 86400);
    }
}