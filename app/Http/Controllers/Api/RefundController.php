<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
class RefundController extends Controller
{
//    public function __construct()
//    {
//        $all=request()->all();
//        if (empty($all['uid'])||empty($all['token'])) {
//            return $this->rejson(201,'登陆失效');
//        }
//        $check=$this->checktoten($all['uid'],$all['token']);
//        if ($check['code']==201) {
//            return $this->rejson($check['code'],$check['msg']);
//        }
//    }
    /**
     * @api {post} /api/refund/reason 退款原因
     * @apiName reason
     * @apiGroup refund
     * @apiParam {string} page 分页（非必填）
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "msg":"查询成功",
     *       "data": {
        "name":"退货理由"
     *      }
     */
    public function reason(){
        $all = \request() -> all();
        $num = 5;
        if (isset($all['page'])) {
            $pages=($all['page']-1)*$num;
        }else{
            $pages=0;
        }

        $data = DB::table('refund_reason')
            -> where('is_del',0)
            -> select('name')
            -> offset($pages)
            -> limit($num)
            -> get();
        return $this->rejson(200,'查询成功',$data);
    }
    /**
     * @api {post} /api/refund/apply 申请退款
     * @apiName apply
     * @apiGroup refund
     * @apiParam {string} uid       用户id     （必填）
     * @apiParam {string} token     验证       （必填）
     * @apiParam {string} order_id  订单id     （必填）
     * @apiParam {string} reason_id 退款原因id （必填）
     * @apiParam {string} money     退款总金额 （必填）
     * @apiParam {string} content   退款说明   （选填）
     * @apiParam {string} image     图片       （选填）
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "msg":"提交成功",
     *       "data": ""
     */
    public function apply(){
        $all = \request() -> all();
        $check=$this->checktoten($all['uid'],$all['token']);
        if ($check['code']==201) {
            return $this->rejson($check['code'],$check['msg']);
        }
        if (empty($all['order_id']) || empty($all['reason_id']) || empty($all['money']) ) {
            return $this->rejson(201,'缺少必要参数');
        }
        $image[] = $all['image'];
        // 获取提交的数据
        $data = [
            'order_id' => $all['order_id'],
            'returns_amount' => $all['money'],
            'reason_id' => $all['reason_id'],
            'status' => 2,
            'content' => $all['content'] ? $all['content'] : '该用户没有填写退款说明',
            'image' => json_encode($image),
            'created_time' => date("Y-m-d H:i:s"),
            'is_reg' => 0,
            'returns_type' => 0,
        ];
        // 链接数据库，新增数据库
        $i = DB::table('order_returns') -> insert($data);
        if($i){
            return $this->rejson(200,'退款申请提交成功');
        }else{
            return $this->rejson(200,'退款申请提交失败，请重试');
        }

    }
    /**
     * @api {post} /api/refund/return_goods 申请退货
     * @apiName return_goods
     * @apiGroup refund
     * @apiParam {string} uid       用户id     （必填）
     * @apiParam {string} token     验证       （必填）
     * @apiParam {string} order_id  订单id     （必填）
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "msg":"查询成功",
     *       "data": {
        "created_time":"申请时间",
        "consignee_realname":"收货人",
        "consignee_telphone":"联系电话",
        "return_address":"退货地址"
        "reason_name":"退货原因"
     * }
     */
    public function return_goods(){
        $all = \request() -> all();
        $check=$this->checktoten($all['uid'],$all['token']);
        if ($check['code']==201) {
            return $this->rejson($check['code'],$check['msg']);
        }
        if (empty($all['order_id'])) {
            return $this->rejson(201,'缺少必要参数');
        }
        // 链接数据库，查询退货表
        $data = DB::table('order_returns')
            -> join('refund_reason','order_returns.reason_id','=','refund_reason.id')
            -> join('merchants','order_returns.merchant_id','=','merchants.id')
            -> join('order_goods','order_returns.order_id','=','order_goods.order_id')
            -> where('order_returns.order_id',$all['order_id'])
            -> select('created_time','consignee_realname','consignee_telphone','merchants.return_address','refund_reason.name as reason_name')
            -> first();
        return $this->rejson(200,'查询成功',$data);
    }
// W83tVnay3ZPCsMA
}