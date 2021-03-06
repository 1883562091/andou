<?php

namespace App\Http\Controllers\Api;

use App\Common\WeChat\WeChatPay;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class UsersController extends Controller {
    public function __construct() {
        $all = request()->all();
        $token = request()->header('token') ?? '';
        if ($token != '') {
            $all['token'] = $token;
        }
        if (empty($all['uid']) || empty($all['token'])) {
            return $this->rejson(202, '登陆失效');
        }
        $check = $this->checktoten($all['uid'], $all['token']);
        if ($check['code'] == 202) {
            return $this->rejson($check['code'], $check['msg']);
        }
    }

    /**
     * @api {post} /api/users/merchant_record 商家浏览记录
     * @apiName merchant_record
     * @apiGroup users
     * @apiParam {string} uid 用户id
     * @apiParam {string} token 验证登陆
     * @apiParam {string} page 查询页码(不是必传
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "data":  [
     * {
     * "id": "商户id",
     * "created_at": "创建时间",
     * "stars_all": "星级",
     * "praise_num":"点赞数量",
     * "logo_img":"商家图片",
     * "name":"商家名字",
     * "address":"商家地址",
     * "tel":"商家电话",
     * "merchant_type_id":"商户类型id"
     * }
     * ],
     *       "msg":"查询成功"
     *     }
     */
    public function merchantRecord() {
        $all = request()->all();
        $num = 10;
        $start = 0;
        if (!empty($all['page'])) {
            $page = $all['page'];
            $start = $num * ($page - 1);
        }
        $data = DB::table('see_log as c')
            ->join('merchants as m', 'm.id', '=', 'c.pid')
            ->where(['c.user_id' => $all['uid'], 'c.type' => 2, 'c.status' => 1])
            ->select('m.id', 'm.address', 'm.merchant_type_id', 'm.tel', 'm.stars_all', 'm.praise_num', 'm.name', 'm.logo_img')
            ->orderByDesc('c.id')
            ->offset($start)
            ->limit($num)
            ->get();
        return $this->rejson(200, '查询成功', $data);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @api {post} /api/users/delete_record 删除浏览记录
     * @apiName delete_record
     * @apiGroup users
     * @apiParam {Number} uid 用户id
     * @apiParam {Number[]} ids 列表 ID
     * @apiSuccessExample Success-Response:
     * {}
     */
    public function deleteRecord(Request $request) {
        $data = $this->validate($request, [
            'uid' => 'required|numeric|exists:users,id',
            'ids' => 'required|array'
        ]);

        $ids = array_values($data['ids']);
        DB::table('see_log')
            ->where('user_id', $data['uid'])
            ->whereIn('pid', $ids)
            ->update(['status' => -1, 'updated_at' => Carbon::now()->toDateTimeString()]);
        return $this->responseJson(200, '删除成功');
    }

    /**
     * @api {post} /api/users/invitations 邀请码 邀请二维码
     * @apiName invitations
     * @apiGroup users
     * @apiParam {string} uid 用户id
     * @apiParam {string} token 验证登陆
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "data":  [
     * {
     * "id": "用户id",
     * "name": "用户名字",
     * "avator": "用户头像",
     * "invitation": "邀请码",
     * "qrcode": "邀请二维码"
     * }
     * ],
     *       "msg":"查询成功"
     *     }
     */
    public function invitations() {
        $all = request()->all();
        $id = $all['uid'];
        $data = DB::table('users')->where('id', $id)->select('id', 'name', 'avator', 'invitation', 'qrcode')->first();
        if ($data->invitation == '0') {
            $data->invitation = $this->invitation($data->id);
        }
        if (empty($data->qrcode)) {
            $data->qrcode = $this->qrcode($data->id);
        }
        return $this->rejson(200, '查询成功', $data);
    }

    /**
     * @api {post} /api/users/binding 绑定上级
     * @apiName binding
     * @apiGroup users
     * @apiParam {string} uid 用户id
     * @apiParam {string} token 验证登陆
     * @apiParam {string} code 上级邀请码
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "data": "",
     *       "msg":"查询成功"
     *     }
     */
    public function binding() {
        $all = request()->all();
        if (empty($all['code'])) {
            return $this->rejson(201, '缺少参数');
        }
        $ress = DB::table('users')->where('id', $all['uid'])->select('guide_puser_id')->first();
        if ($ress->guide_puser_id > 0) {
            return $this->rejson(201, '你已经存在上级用户');
        }
        $re = DB::table('users')->where('invitation', $all['code'])->select('id')->first();
        if (empty($re)) {
            return $this->rejson(201, '邀请码不存在');
        }
        $data['guide_puser_id'] = $re->id;
        $res = DB::table('users')->where('id', $all['uid'])->update($data);
        if ($res) {
            return $this->rejson(200, '绑定成功');
        } else {
            return $this->rejson(201, '绑定失败');
        }
    }

    /**
     * @api {post} /api/users/collection 商品收藏记录
     * @apiName collection
     * @apiGroup users
     * @apiParam {string} uid 用户id
     * @apiParam {string} token 验证登陆
     * @apiParam {string} page 查询页码(不是必传
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "data":  [
     * {
     * "id": "商品id",
     * "created_at": "创建时间",
     * "price": "商品价格",
     * "img":"商品图片",
     * "name":"商品名字"
     * }
     * ],
     *       "msg":"查询成功"
     *     }
     */
    public function collection() {
        $all = request()->all();
        $num = 10;
        $start = 0;
        if (!empty($all['page'])) {
            $page = $all['page'];
            $start = $num * ($page - 1);
        }
        $data = DB::table('collection as c')
            ->join('goods as m', 'm.id', '=', 'c.pid')
            ->where(['c.user_id' => $all['uid'], 'c.type' => 1])
            ->select('m.id', 'm.price', 'm.img', 'm.name')
            ->orderBy('c.id', "DESC")
            ->offset($start)
            ->limit($num)
            ->get();
        return $this->rejson(200, '查询成功', $data);
    }

    /**
     * @api {post} /api/users/follow 商家关注记录
     * @apiName follow
     * @apiGroup users
     * @apiParam {string} uid 用户id
     * @apiParam {string} token 验证登陆
     * @apiParam {string} page 查询页码(不是必传
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "data":  [
     * {
     * "id": "商户id",
     * "created_at": "创建时间",
     * "stars_all": "星级",
     * "praise_num":"点赞数量",
     * "logo_img":"商家图片",
     * "name":"商家名字",
     * "address":"商家地址",
     * "merchant_type_id":"商家类型id",
     * "tel":"商家电话"
     * }
     * ],
     *       "msg":"查询成功"
     *     }
     */
    public function follow() {
        $all = request()->all();
        $num = 10;
        $start = 0;
        if (!empty($all['page'])) {
            $page = $all['page'];
            $start = $num * ($page - 1);
        }
        $data = DB::table('collection as c')
            ->join('merchants as m', 'm.id', '=', 'c.pid')
            ->where(['c.user_id' => $all['uid'], 'c.type' => 3])
            ->select('m.id', 'm.address', 'm.merchant_type_id', 'm.tel', 'm.stars_all', 'm.praise_num', 'm.name', 'm.logo_img')
            ->orderBy('c.id', "DESC")
            ->offset($start)
            ->limit($num)
            ->get();
        return $this->rejson(200, '查询成功', $data);
    }

    /**
     * @api {post} /api/users/fabulous 给商家点赞
     * @apiName fabulous
     * @apiGroup users
     * @apiParam {string} uid 用户id
     * @apiParam {string} token 验证登陆
     * @apiParam {string} id 商家id
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "data": "",
     *       "msg":"查询成功"
     *     }
     */
    public function fabulous() {
        $all = request()->all();
        if (empty($all['id'])) {
            return $this->rejson(201, '缺少参数');
        }
        $data['user_id'] = $all['uid'];
        $data['pid'] = $all['id'];
        $data['created_at'] = date('Y-m-d H:i:s', time());
        $datas = DB::table('fabulous')->where(['user_id' => $all['uid'], 'pid' => $all['id']])->first();

        if (empty($datas)) {
            $re = DB::table('fabulous')->insert($data);
            $res = DB::table('merchants')->where('id', $all['id'])->increment('praise_num');
            return $this->rejson(200, '点赞成功');
        } else {
            return $this->rejson(201, '不能重复点赞');
        }
    }

    /**
     * @api {post} /api/users/envelopes 红包金额查询
     * @apiName envelopes
     * @apiGroup users
     * @apiParam {string} uid 用户id
     * @apiParam {string} token 验证登陆
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "data": {
     *               "value":"领取金额"
     *        },
     *       "msg":"查询成功"
     *     }
     */
    public function envelopes() {
        $all = request()->all();
        $re = DB::table('user_logs')->where(['user_id' => $all['uid'], 'type_id' => '4'])->first();
        if (!empty($re)) {
            return $this->rejson(201, '该用户已经领取过新用户红包');
        }
        $data = DB::table('config')->select('value')->where('key', 'envelopes')->first();
        return $this->rejson(200, '获取成功', $data);
    }

    /**
     * @api {post} /api/users/new_user 查询是否为新用户
     * @apiName new_user
     * @apiGroup users
     * @apiParam {string} uid 用户id
     * @apiParam {string} token 验证登陆
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "data": {"val":1}(1是新用户，0不是新用户),
     *       "msg":"该用户是新用户"
     *     }
     */
    public function new_user() {
        $all = request()->all();
        $re = DB::table('user_logs')->where(['user_id' => $all['uid'], 'type_id' => '4'])->first();
        if (!empty($re)) {
            return $this->rejson(200, '该用户已经领取过新用户红包', ['val' => 0]);
        } else {
            return $this->rejson(200, '该用户是新用户', ['val' => 1]);
        }
    }

    /**
     * @api {post} /api/users/envelopes_add 新用户领取红包
     * @apiName envelopes_add
     * @apiGroup users
     * @apiParam {string} uid 用户id
     * @apiParam {string} token 验证登陆
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "data": "",
     *       "msg":"领取成功"
     *     }
     */
    public function envelopesAdd() {
        // TODO: uid 存在问题
        $all = request()->all();
        if (!isset($all['uid']) || !$all['uid']) {
            return $this->responseJson(201, '用户信息不存在');
        }
        $re = DB::table('user_logs')->where(['user_id' => $all['uid'], 'type_id' => '4'])->first();
        if (!empty($re)) {
            return $this->rejson(201, '该用户已经领取过新用户红包');
        }
        $data['price'] = DB::table('config')->where('key', 'envelopes')
                ->select('value')
                ->first()
                ->value ?? '';
        if ($data['price'] == '') {
            return $this->rejson(201, '系统错误');
        }
        $data['user_id'] = $all['uid'];
        $data['describe'] = '新用户红包领取';
        $data['create_time'] = date('Y-m-d H:i:s', time());
        $data['type_id'] = 4;
        $data['state'] = 1;
        $data['is_del'] = 0;
        DB::beginTransaction(); //开启事务
        $re = DB::table('user_logs')->insert($data);
        $res = DB::table('users')->where('id', $all['uid'])->increment('money', $data['price']);
        if ($res && $re) {
            DB::commit();
            return $this->rejson(200, '领取成功');
        } else {
            DB::rollback();
            return $this->rejson(201, '领取失败');
        }
    }

    /**
     * @api {post} /api/users/upmodel 修改手机号
     * @apiName upmodel
     * @apiGroup users
     * @apiParam {string} uid 用户id
     * @apiParam {string} token 验证登陆
     * @apiParam {string} phone 手机号码
     * @apiParam {string} verify 验证码
     *
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "data": "",
     *       "msg":"修改成功"
     *     }
     */
    public function upmodel() {
        $all = request()->all();
        if (empty($all['phone']) || empty($all['verify']) || empty($all['uid'])) {
            return $this->rejson(201, '参数错误');
        }
        $data['mobile'] = $all['phone'];
        if ($all['verify'] != Redis::get($all['phone'])) {
            return $this->rejson(201, '验证码错误');
        }
        $re = DB::table('users')->where('id', $all['uid'])->update($data);
        return $this->rejson('200', "修改手机号成功");
    }

    /**
     * @api {post} /api/users/vip_recharge vip购买
     * @apiName vip_recharge
     * @apiGroup users
     * @apiParam {string} uid 用户id
     * @apiParam {string} token 验证登陆
     * @apiParam {string} pay_id 支付方式id
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "data": "",
     *       "msg":"修改成功"
     *     }
     */
    public function vipRecharge() {
        $all = request()->all();
        $price = DB::table('config')->where('key', 'vipRecharge')->first()->value ?? 0;
        if (!$price) {
            return $this->rejson(201, '后台未设置会员开通价格');
        }
        $data['user_id'] = $all['uid'];
        $data['price'] = $price;
        $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s', time());
        $data['order_sn'] = $this->suiji();
        $re = DB::table('vip_recharge')->insert($data);
        if ($all['pay_id'] == 1) {//微信支付
            return $this->responseJson(200, 'OK', $this->wxpay($data['order_sn']));
        } else if ($all['pay_id'] == 2) {//支付宝支付
            return $this->rejson(201, '暂未开通');
        } else if ($all['pay_id'] == 3) {//银联支付
            return $this->rejson(201, '暂未开通');
        } else if ($all['pay_id'] == 5) {//其他支付
            return $this->rejson(201, '暂未开通');
        } else {
            return $this->rejson(201, '暂未开通');
        }
    }

    /**
     * @api {post} /api/users/vip_rote 会员规者和金额
     * @apiName vip_rote
     * @apiGroup users
     * @apiParam {string} uid 用户id
     * @apiParam {string} token 验证登陆
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "data": "",
     *       "msg":"修改成功"
     *     }
     */
    public function vipRote() {
        $all = request()->all();
        $data['price'] = DB::table('config')->where('key', 'vipRecharge')->first()->value ?? 0;
        if (!$data['price']) {
            return $this->rejson(201, '后台未设置会员开通价格');
        }
        $data['vip_rote'] = DB::table('vip_equity')->first()->content ?? 0;
        return $this->rejson(200, '获取成功', $data);
    }

    public function wxPay($sNo) {
        $orders = DB::table('vip_recharge')
            ->where('order_sn', $sNo)
            ->first();
        if (empty($orders)) {
            return $this->rejson(201, '订单不存在');
        }
        $pay_money = 100 * $orders->price;

        return WeChatPay::getInstance()->copy()
            ->resetGateway('WechatPay_App', 'http://andou.zhuosongkj.com/api/common/viprecharge')
            ->createOrder(
                $sNo,
                $pay_money,
                '安抖本地生活-消费',
                'VIP 充值',
                request()->ip(),
                Carbon::now()->addHour()->format('YmdHis')
            );
    }
}
