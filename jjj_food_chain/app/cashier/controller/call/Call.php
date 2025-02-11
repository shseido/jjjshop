<?php

namespace app\cashier\controller\call;

use app\common\model\call\Call as CallModel;
use hg\apidoc\annotation as Apidoc;
use app\cashier\controller\Controller;

/**
 * 呼叫相关
 * @Apidoc\Group("base")
 * @Apidoc\Sort(4)
 */
class Call extends Controller
{
    /**
     * @Apidoc\Title("呼叫列表")
     * @Apidoc\Desc("呼叫列表 - 前端定时每秒查询一次")
     * @Apidoc\Method("POST")
     * @Apidoc\Url("/index.php/cashier/call.call/index")
     * @Apidoc\Param("status", type="int", require=false, default="0", desc="状态：0未处理，1已处理")
     * @Apidoc\Param(ref="pageParam")
     * @Apidoc\Returned("list",type="array",ref="app\common\model\call\Call\getList")
     */
    public function index($status)
    {
        $list = (new CallModel)->getList($this->postData(), $status, $this->cashier['user']['shop_supplier_id'] ?? 0);
        return $this->renderSuccess('', compact('list'));
    }

    /**
     * @Apidoc\Title("已处理")
     * @Apidoc\Desc("已处理")
     * @Apidoc\Method("POST")
     * @Apidoc\Url("/index.php/cashier/call.call/processed")
     * @Apidoc\Param("call_id", type="int", require=false, default="0", desc="呼叫id")
     */
    public function processed($call_id)
    {
        //
        (new CallModel)->markAsProcessed($call_id, $this->cashier['user']['shop_supplier_id'] ?? 0);
        //
        return $this->renderSuccess('成功');
    }

    /**
     * @Apidoc\Title("未处理数量")
     * @Apidoc\Desc("未处理数量")
     * @Apidoc\Method("POST")
     * @Apidoc\Url("/index.php/cashier/call.call/unprocessed")
     * @Apidoc\Returned("count", type="int", desc="未处理数量")
     * @Apidoc\Returned("list", type="array", desc="推送消息列表", ref="app\common\model\call\Call\getUnSendList", children={
     *    @Apidoc\Param("call_text", type="string", desc="呼叫文字"),
     * })
     */
    public function unprocessed()
    {
        // 未处理数量
        $count = (new CallModel)->getUnprocessedCount($this->cashier['user']['shop_supplier_id'] ?? 0);
        // 未发送消息列表
        $list = (new CallModel)->getUnSendList($this->cashier['user']['shop_supplier_id'] ?? 0);
        // 
        $time = time();
        // 
        return $this->renderSuccess('', compact('count', 'list', 'time'));
    }
}