<?php

namespace App\Http\Controllers\Api;

use App\Common\Ali\Alipay;
use App\Common\WeChat\WeChatPay;
use App\Http\Controllers\Controller;
use App\Jobs\Order\AutoCancel;
use App\Models\Goods;
use App\Models\OrderCancel;
use App\Models\OrderCancelReason;
use App\Models\OrderGoods;
use App\Models\Orders;
use App\Models\UserAddress;
use App\Models\Users;
use App\Services\GroupService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller {
    public function __construct() {
        $all = request()->all();
        $token = request()->header('token') ?? '';

        if (!empty($token)) {
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
     * @api {post} /api/order/orderReturn 申请退款列表
     * @apiName orderReturn
     * @apiGroup order
     * @apiParam {string} uid 用户id
     * @apiParam {string} token 验证登陆
     * @apiParam {string} id 订单子id
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "data": [
     * {
     * "img": "商品图",
     * "name": "商品名字",
     * "desc": "商品简介",
     * "num": "数量",
     * "price": "单价",
     * "attr_value": [
     * "4G+32G",
     * "纸包装",
     * "白"
     * ]
     * }
     * ],
     *       "msg":"查询成功"
     *     }
     */

    public function orderReturn() {
        $all = request()->all();
        if (empty($all['id'])) {
            return $this->rejson('201', '缺少参数');
        }
        $data = DB::table('order_goods')
            ->join('goods', 'order_goods.goods_id', '=', 'goods.id')
            ->join('goods_sku as s', 's.id', '=', 'order_goods.goods_sku_id')
            ->where('order_goods.id', $all['id'])
            ->select('order_goods.num', 'goods.name', 'goods.desc', 'goods.img', 's.attr_value', 's.price')
            ->get();
        foreach ($data as $k => $v) {
            $data[$k]->attr_value = json_decode($v->attr_value, 1)[0]['value'];
        }
        return $this->rejson('200', '查询成功', $data);


    }

    /**
     * @api {post} /api/order/index 订单列表
     * @apiName index
     * @apiGroup order
     * @apiParam {string} uid 用户id
     * @apiParam {string} token 验证登陆
     * @apiParam {string} type 状态(非比传 10-未支付 20-已支付 40-已发货  50-交易成功（确认收货） 60-交易关闭（已评论）)
     * @apiParam {string} page 查询页码
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "data": [
     * {
     * "img": "商品图",
     * "name": "商品名字",
     * "goods_id": "商品id",
     * "merchant_id": "商户id",
     * "order_id": "订单号",
     * "status": "状态 10-未支付 20-已支付 40-已发货  50-交易成功（确认收货） 60-交易关闭（已评论）",
     * "mname": "商家名字",
     * "logo_img": "商家图",
     * "num": "数量",
     * "id": "订单id",
     * "express_id":"快递公司id",
     * "courier_num":"快递单号",
     * "shipping_free": "运费",
     * "price": "单价",
     * "pay_money": "总价",
     * "attr_value": [
     * "4G+32G",
     * "纸包装",
     * "白"
     * ]
     * }
     * ],
     *       "msg":"添加成功"
     *     }
     */
    public function index() {
        $all = request()->all();
        $num = 10;
        if (isset($all['page'])) {
            $pages = ($all['page'] - 1) * $num;
        } else {
            $pages = 0;
        }

        $where[] = ['o.user_id', $all['uid']];

        if (isset($all['type'])) {
            $where[] = ['o.status', $all['type']];
        }

        $data = DB::table('order_goods as o')
            ->join('goods as g', 'g.id', '=', 'o.goods_id')
            ->join('merchants as m', 'm.id', '=', 'o.merchant_id')
            ->join('goods_sku as s', 's.id', '=', 'o.goods_sku_id')
            ->where($where)
            ->select('g.img', 'g.name', 'o.goods_id', 'o.merchant_id', 'o.order_id', 'o.status', 'm.name as mname', 'm.logo_img', 'o.num', 'o.id', 'shipping_free', 'o.express_id', 'o.courier_num', 's.price', 'pay_money', 's.attr_value')
            ->orderBy('o.created_at', 'DESC')
            ->offset($pages)
            ->limit($num)
            ->get();
        foreach ($data as $k => $v) {
            $data[$k]->attr_value = json_decode($v->attr_value, 1)[0]['value'];
        }

        return $this->rejson(200, '查询成功', $data);
    }

    /**
     * @api {post} /api/order/express 快递查询
     * @apiName express
     * @apiGroup order
     * @apiParam {string} uid 用户id
     * @apiParam {string} token 验证登陆
     * @apiParam {string} express_id 快递公司id
     * @apiParam {string} courier_num 快递单号
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "data": "",
     *       "msg":"添加成功"
     *     }
     */
    public function express() {

        $customer = "A85FFAADEF1E377FC67275CB15698F72";

        $key = 'HZdwfXDv3190';

        $url = 'http://poll.kuaidi100.com/poll/query.do';
        $all = request()->all();
        $express_id = $all['express_id'];
        $courier_num = $all['courier_num'];

        if (!empty($express_id) && !empty($courier_num)) {

            $r01 = DB::table('express')->where('id', $express_id)->first();
            $type = $r01->com; //快递公司代码

            $kuaidi_name = $r01->name;

            $post_data["customer"] = $customer;

            $post_data["param"] = '{"com":"' . $type . '","num":"' . $courier_num . '"}';

            $post_data["sign"] = md5($post_data["param"] . $key . $post_data["customer"]);

            $post_data["sign"] = strtoupper($post_data["sign"]);

            $o = "";

            foreach ($post_data as $k => $v) {

                $o .= "$k=" . urlencode($v) . "&";  //默认UTF-8编码格式
            }

            $post_data = substr($o, 0, -1);

            //发起CURL请求

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_POST, 1);

            curl_setopt($ch, CURLOPT_HEADER, 0);

            curl_setopt($ch, CURLOPT_URL, $url);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $result = curl_exec($ch);

            $da = str_replace("\"", '"', $result);

            $res_1 = json_decode($da, true);

            $data['wuliu_msg'] = $res_1;

            $data['name'] = $kuaidi_name;

            $data['courier_num'] = $courier_num;

            return $this->rejson(200, '获取信息成功！', $data);
        } else {
            return $this->rejson(201, '未查询到物流信息！');
        }

    }

    /**
     * @throws Exception
     * @api {post} /api/order/add_order 立即购买
     * @apiName add_order
     * @apiGroup order
     * @apiParam {string} uid 用户id
     * @apiParam {string} token 验证登陆
     * @apiParam {string} goods_id 商品id
     * @apiParam {string} merchant_id 商户id
     * @apiParam {stringstring} goods_sku_id 规格id
     * @apiParam {string} num 购买数量
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "data": {
     * "order_sn": "订单号"
     * }
     *       "msg":"添加成功"
     *     }
     */
    public function addOrder() {
        $all = request()->all();
        if (empty($all['goods_id']) || empty($all['merchant_id']) || empty($all['goods_sku_id']) || empty($all['num'])) {
            return $this->rejson(201, '缺少参数');
        }
        $address = DB::table('user_address')->where(['user_id' => $all['uid'], 'is_defualt' => 1])->first();
        if (empty($address)) {
            return $this->rejson(201, '请填写收货地址');
        } else {
            $alldata['address_id'] = $address->id;
        }
        $data['goods_id'] = $all['goods_id'];
        $alldata['status'] = 10;
        $data['status'] = 10;
        $data['merchant_id'] = $all['merchant_id'];
        $data['goods_sku_id'] = $all['goods_sku_id'];
        $data['num'] = $all['num'];
        $data['pay_discount'] = 1;
        $alldata['user_id'] = $data['user_id'] = $all['uid'];
        $alldata['order_sn'] = $data['order_id'] = app('Snowflake\Snowflake')->next();
        $alldata['created_at'] = $alldata['updated_at'] = $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s', time());
        $dilivery = DB::table('goods')->select('dilivery', 'weight')->where('id', $all['goods_id'])->first();
        if ($dilivery->dilivery > 0) {
            $alldata['shipping_free'] = $data['shipping_free'] = $this->freight($dilivery->weight * $all['num'], $all['num'], $dilivery->dilivery);
        } else {
            $alldata['shipping_free'] = $data['shipping_free'] = 0;
        }

        $datas = DB::table('goods_sku')->where('id', $all['goods_sku_id'])->where('store_num', '>', 0)->first();
        if (empty($data)) {
            return $this->rejson(201, '商品库存不足');
        }
        $alldata['order_money'] = $data['pay_money'] = $datas->price * $all['num'] * $data['pay_discount'] + $data['shipping_free'];
        $data['total'] = $datas->price * $all['num'] + $data['shipping_free'];
        $alldata['type'] = 1;
        $alldata['remark'] = $all['remark'] ?? '';
        $alldata['auto_receipt'] = $all['auto_receipt'] ?? 0;
        DB::beginTransaction(); //开启事务
        $re = DB::table('order_goods')->insert($data);
        $res = DB::table('orders')->insert($alldata);
        $ret = DB::table('goods')->where('id', $all['goods_id'])->increment('volume');
        if ($res && $re && $ret) {
            DB::commit();
            // 30 分钟自动关闭订单
            AutoCancel::dispatch($data['order_id'])
                ->delay(Carbon::now()->addMinutes(30))->onQueue('OrderAutoCancel');
            return $this->rejson(200, '下单成功', ['order_sn' => $data['order_id']]);
        } else {
            DB::rollback();
            return $this->rejson(201, '下单失败');
        }
    }

    /**
     * @api {post} /api/order/add_order_car 购物车购买
     * @apiName add_order_car
     * @apiGroup order
     * @apiParam {string} uid 用户id
     * @apiParam {string} token 验证登陆
     * @apiParam {array}  id 购物车id
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "data": {
     * "order_sn": "订单号"
     * }
     *       "msg":"添加成功"
     *     }
     */
    public function addOrderCar() {
        $all = request()->all();
        $all['id'] = explode(',', $all['id']);
        // $all['id']= '1,2';
        if (empty($all['id'])) {
            return $this->rejson(201, '缺少参数');
        }
        $address = DB::table('user_address')->where(['user_id' => $all['uid'], 'is_defualt' => 1])->first();
        if (empty($address)) {
            return $this->rejson(201, '请填写收货地址');
        } else {
            $alldata['address_id'] = $address->id;
            $alldata['status'] = 10;
            $alldata['order_money'] = 0;
            $alldata['type'] = 1;
            $alldata['remark'] = $all['remark'] ?? '';
            $alldata['order_sn'] = $data['order_id'] = $this->suiji();
            $alldata['user_id'] = $data['user_id'] = $all['uid'];
            $alldata['shipping_free'] = 0;
            $alldata['created_at'] = $alldata['updated_at'] = $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s', time());
            $alldata['auto_receipt'] = $all['auto_receipt'] ?? 0;
        }
        DB::beginTransaction(); //开启事务
        foreach ($all['id'] as $v) {
            $car = DB::table('cart')//查询购物车
            ->where(['id' => $v, 'user_id' => $all['uid']])
                ->first();
            if (empty($car)) {
                DB::rollback();
                return $this->rejson(201, '购物车id不存在');
            }
            $data['num'] = $car->num ?? 0;
            $dilivery = DB::table('goods')->select('dilivery', 'weight')->where('id', $car->goods_id)->first();
            if ($dilivery->dilivery > 0) {
                $data['shipping_free'] = $this->freight($dilivery->weight * $data['num'], $data['num'], $dilivery->dilivery);
                $alldata['shipping_free'] += $data['shipping_free'];
            } else {
                $data['shipping_free'] = 0;
            }
            $datas = DB::table('goods_sku')->where('id', $car->goods_sku_id)->where('store_num', '>', 0)->first();
            if (empty($datas)) {
                DB::rollback();
                return $this->rejson(201, '商品库存不足');
            }

            $data['goods_id'] = $car->goods_id;
            $data['status'] = 10;
            $data['merchant_id'] = $car->merchant_id;
            $data['goods_sku_id'] = $car->goods_sku_id;

            $data['pay_discount'] = 1;
            $alldata['order_money'] += $data['pay_money'] = $datas->price * $data['num'] * $data['pay_discount'] + $data['shipping_free'];
            $data['total'] = $datas->price * $data['num'] + $data['shipping_free'];

            $re = DB::table('order_goods')->insert($data);

            if (!$re) {
                DB::rollback();
                return $this->rejson(201, '下单失败');
            }
        }

        $res = DB::table('orders')->insert($alldata);

        $red = DB::table('cart')->where('user_id', $all['uid'])->whereIn('id', $all['id'])->delete();

        if ($res && $red) {
            DB::commit();
            return $this->rejson(200, '下单成功', ['order_sn' => $data['order_id']]);
        } else {
            DB::rollback();
            return $this->rejson(201, '下单失败');
        }
    }

    /**
     * @api {post} /api/order/settlement 购买结算页
     * @apiName settlement
     * @apiGroup order
     * @apiParam {string} uid 用户id
     * @apiParam {string} token 验证登陆
     * @apiParam {array}  order_sn 订单号
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "data": {
     * "order_money": "订单总金额",
     * "id": 订单id,
     * "order_sn": "订单号",
     * "address_id": "收货地址id",
     * "integral":"使用积分",
     * "userinfo": {
     * "name": "收货人",
     * "address": "收货详细地址",
     * "mobile": "收货人电话",
     * "province": "省",
     * "city": "市",
     * "area": "区"
     * },
     * "details": [
     * {
     * "img": "商品图片",
     * "name": "名字",
     * "num": "购买数量",
     * "shipping_free": "单商品邮费",
     * "price": "单价",
     * "attr_value": [//规格
     * "4G+32G",
     * "精包装",
     * "白"
     * ]
     * }
     * ],
     * "shipping_free": "总运费"
     * }
     *       "msg":"添加成功"
     *     }
     */
    public function settlement() {
        $all = request()->all();
        if (empty($all['order_sn'])) {
            return $this->rejson(201, '缺少参数');
        }
        $data = DB::table('orders')
            ->where(['order_sn' => $all['order_sn'], 'user_id' => $all['uid'], 'type' => 1, 'is_del' => 0])
            ->select('order_money', 'id', 'shipping_free', 'order_sn', 'address_id')
            ->first();
        if (empty($data)) {
            return $this->rejson(201, '无效的订单号');
        }

        $percent = DB::table('config')->where('key', 'integral')->value('value');
        $max_deduction = floor(($data->order_money - $data->shipping_free) * $percent);
//        $integral = DB::table('users')->find($all['uid'])->value('integral');
        $integral = DB::table('users')->where('id', $all['uid'])->value('integral');

        if ($integral >= $max_deduction) {
            $data->integral = $max_deduction;
        } else {
            $data->integral = $integral;
        }

        $address = DB::table('user_address')
            ->where('id', $data->address_id)
            ->first();
        $province = DB::table('districts')->where('id', $address->province_id)->first()->name ?? '';
        $city = DB::table('districts')->where('id', $address->city_id)->first()->name ?? '';
        $area = DB::table('districts')->where('id', $address->area_id)->first()->name ?? '';
        $data->userinfo = ['name' => $address->name, 'address' => $address->address, 'mobile' => $address->mobile, 'province' => $province, 'city' => $city, 'area' => $area];
        $data->details = DB::table('order_goods as o')
            ->join('goods as g', 'g.id', '=', 'o.goods_id')
            ->join('goods_sku as s', 's.id', '=', 'o.goods_sku_id')
            ->where('o.order_id', $all['order_sn'])
            ->select('g.img', 'g.name', 'o.num', 'shipping_free', 's.price', 's.attr_value')
            ->get();
        $data->shipping_free = 0;
        foreach ($data->details as $key => $value) {
            $data->details[$key]->attr_value = json_decode($value->attr_value, 1)[0]['value'];
            $data->shipping_free += $value->shipping_free;
        }
        return $this->rejson(200, '查询成功', $data);
    }

    /**
     * @api {post} /api/order/details 订单详情
     * @apiName details
     * @apiGroup order
     * @apiParam {string} uid 用户id
     * @apiParam {string} token 验证登陆
     * @apiParam {string}  order_sn 订单编号
     * @apiParam {string}  did 子订单id
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "data": {
     * "order_money": "订单总金额",
     * "id": 订单id,
     * "order_sn": "订单号",
     * "address_id": "收货地址id",
     * "integral":"使用积分",
     * "pay_money":"支付金额",
     * "pay_time":"付款时间",
     * "status":"订单状态",
     * "allnum":"购买商品总数",
     * "userinfo": {
     * "name": "收货人",
     * "address": "收货详细地址",
     * "mobile": "收货人电话",
     * "province": "省",
     * "city": "市",
     * "area": "区"
     * },
     * "details": [
     * {
     * "id": "订单编号id",
     * "img": "商品图片",
     * "name": "名字",
     * "num": "购买数量",
     * "shipping_free": "单商品邮费",
     * "price": "单价",
     * "attr_value": [//规格
     * "4G+32G",
     * "精包装",
     * "白"
     * ]
     * }
     * ],
     * "shipping_free": "总运费"
     * }
     *       "msg":"添加成功"
     *     }
     */
    public function details() {
        $all = request()->all();
        if (empty($all['order_sn'])) {
            return $this->rejson(201, '缺少参数');
        }
        $data = DB::table('orders')
            ->where(['order_sn' => $all['order_sn'], 'user_id' => $all['uid'], 'type' => 1, 'is_del' => 0])
            ->select('order_money', 'pay_way', 'pay_money', 'pay_time', 'id', 'integral', 'shipping_free', 'order_sn', 'status', 'address_id')
            ->first();
        if (empty($data)) {
            return $this->rejson(201, '无效的订单号');
        }

        $integral = DB::table('config')->where('key', 'integral')->first()->value;
        $data->integral = floor(($data->order_money - $data->shipping_free) * $integral);
        $address = DB::table('user_address')
            ->where('id', $data->address_id)
            ->first();
        $province = DB::table('districts')->where('id', $address->province_id)->first()->name ?? '';
        $city = DB::table('districts')->where('id', $address->city_id)->first()->name ?? '';
        $area = DB::table('districts')->where('id', $address->area_id)->first()->name ?? '';
        $data->userinfo = ['name' => $address->name, 'address' => $address->address, 'mobile' => $address->mobile, 'province' => $province, 'city' => $city, 'area' => $area];
        $where[] = ["o.order_id", $all['order_sn']];
        if (isset($all['did'])) {
            $where[] = ["o.id", $all['did']];
        }
        $data->details = DB::table('order_goods as o')
            ->join('goods as g', 'g.id', '=', 'o.goods_id')
            ->join('goods_sku as s', 's.id', '=', 'o.goods_sku_id')
            ->where($where)
            ->select('o.id', 'g.img', 'o.status', 'g.name', 'o.num', 'shipping_free', 's.price', 's.attr_value')
            ->get();
        $data->shipping_free = 0;
        $data->allnum = 0;
        foreach ($data->details as $key => $value) {
            $data->details[$key]->attr_value = json_decode($value->attr_value, 1)[0]['value'];
            $data->allnum += $value->num;
            $data->shipping_free += $value->shipping_free;
        }
        return $this->rejson(200, '查询成功', $data);
    }

    /**
     * @api {post} /api/order/wait_goods 待收货
     * @apiName wait_goods
     * @apiGroup order
     * @apiParam {string} uid 用户id
     * @apiParam {string} token 验证登陆
     * @apiParam {int}  order_goods_id 订单编号id
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "data": {
     * "order_money": "订单总金额",
     * "id": 订单id,
     * "order_sn": "订单号",
     * "address_id": "收货地址id",
     * "integral":"使用积分",
     * "pay_money":"支付金额",
     * "pay_time":"付款时间",
     * "status":"订单状态",
     * "allnum":"购买商品总数",
     * "userinfo": {
     * "name": "收货人",
     * "address": "收货详细地址",
     * "mobile": "收货人电话",
     * "province": "省",
     * "city": "市",
     * "area": "区"
     * },
     * "details": [
     * {
     * "id": "订单编号id",
     * "img": "商品图片",
     * "name": "名字",
     * "num": "购买数量",
     * "shipping_free": "单商品邮费",
     * "price": "单价",
     * "attr_value": [//规格
     * "4G+32G",
     * "精包装",
     * "白"
     * ]
     * }
     * ],
     * "shipping_free": "总运费"
     * }
     *       "msg":"添加成功"
     *     }
     */
    public function wait_goods() {
        $all = request()->all();
        if (empty($all['order_goods_id'])) {
            return $this->rejson(201, '缺少参数');
        }
        $order_id = DB::table('order_goods')->where('id', $all['order_goods_id'])->select('order_id')->first();
        $data = DB::table('orders')
            ->where(['order_sn' => $order_id->order_id, 'user_id' => $all['uid'], 'type' => 1, 'is_del' => 0])
            ->select('order_money', 'pay_way', 'pay_money', 'pay_time', 'id', 'integral', 'shipping_free', 'order_sn', 'status', 'address_id')
            ->first();
        if (empty($data)) {
            return $this->rejson(201, '无效的订单号');
        }

        $integral = DB::table('config')->where('key', 'integral')->first()->value;
        $data->integral = floor(($data->order_money - $data->shipping_free) * $integral);
        $address = DB::table('user_address')
            ->where('id', $data->address_id)
            ->first();

        $province = DB::table('districts')->where('id', $address->province_id)->first()->name ?? '';
        $city = DB::table('districts')->where('id', $address->city_id)->first()->name ?? '';
        $area = DB::table('districts')->where('id', $address->area_id)->first()->name ?? '';
        $data->userinfo = ['name' => $address->name, 'address' => $address->address, 'mobile' => $address->mobile, 'province' => $province, 'city' => $city, 'area' => $area];

        $data->details = DB::table('order_goods as o')
            ->join('goods as g', 'g.id', '=', 'o.goods_id')
            ->join('goods_sku as s', 's.id', '=', 'o.goods_sku_id')
            ->where('o.id', $all['order_goods_id'])
            ->select('o.id', 'g.img', 'g.name', 'o.num', 'shipping_free', 's.price', 's.attr_value')
            ->get();
        $data->shipping_free = 0;
        $data->allnum = 0;
        foreach ($data->details as $key => $value) {
            $data->details[$key]->attr_value = json_decode($value->attr_value, 1)[0]['value'];
            $data->allnum += $value->num;
            $data->shipping_free += $value->shipping_free;
        }
        return $this->rejson(200, '查询成功', $data);
    }

    /**
     * @api {post} /api/order/pay 订单支付
     * @apiName pay
     * @apiGroup order
     * @apiParam {string} uid 用户id
     * @apiParam {string} token 验证登陆
     * @apiParam {string} sNo 订单号
     * @apiParam {string} pay_id 支付方式id
     * @apiParam {string} is_integral 是否使用积分 1使用 0不使用
     * @apiParam {number} puzzle_id 可选，团购id，购团商品需要传递
     * @apiParam {number} open_join 团购必填，开团还是参团：1开团 2参团
     * @apiParam {number} group_id 参团必填，参团id
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "data": "",
     *       "msg":"查询成功"
     *     }
     */
    public function pay() {
        $all = request()->all();
        if ($all['pay_id'] == 1) {//微信支付
            return $this->wxpay();
        } else if ($all['pay_id'] == 2) {//支付宝支付
            return $this->aliPay();
        } else if ($all['pay_id'] == 3) {//银联支付
            return $this->rejson(201, '暂未开通');
        } else if ($all['pay_id'] == 4) {//余额支付
            return $this->balancePay();
        } else if ($all['pay_id'] == 5) {//其他支付
            return $this->rejson(201, '暂未开通');
        } else {
            return $this->rejson(201, '暂未开通');
        }
    }

    public function balancePay() {
        $all = request()->all();
        if (empty($all['sNo'])) {
            return $this->rejson(201, '参数错误');
        }
        $sNo = $all['sNo'];
        $users = DB::table('users')
            ->select('money', 'integral')
            ->where('id', $all['uid'])
            ->first();
        $orders = DB::table('orders')
            ->where(['order_sn' => $sNo, 'status' => 10, 'user_id' => $all['uid']])
            ->first();
        if (empty($orders)) {
            return $this->rejson(201, '订单不存在');
        }
        $order_goods = DB::table('order_goods')->where('order_id', $sNo)->get();
        if ($all['is_integral'] == 1) {
            $integrals = DB::table('config')->where('key', 'integral')->first()->value;
            $integral = floor(($orders->order_money - $orders->shipping_free) * $integrals);
            if ($users->integral < $integral) {
                return $this->rejson(201, '积分不足');
            } else {
                foreach ($order_goods as $key => $value) {
                    $uporder['integral'] = floor(($value->pay_money - $value->shipping_free) * $integrals);
                    $uporder['pay_money'] = $value->pay_money - $uporder['integral'];
                    DB::table('order_goods')->where('id', $value->id)->update($uporder);
                }
                $dataintegral['integral'] = $integral;
                DB::table('orders')->where('order_sn', $sNo)->update($dataintegral);
            }
        } else {
            $integral = 0;
        }
        if ($users->money < $orders->order_money - $integral) {
            return $this->rejson(201, '余额不足');
        }

        // hcq新增：团购支付处理
        $puzzle_id = empty($all['puzzle_id']) ? 0 : $all['puzzle_id'];
        $open_join = empty($all['open_join']) ? 0 : $all['open_join'];
        $group_id = empty($all['group_id']) ? 0 : $all['group_id'];
        if ($orders->puzzle_id != 0) {
            if (empty($puzzle_id)) {
                return $this->rejson(201, '团购参数错误');
            }
            $service = new GroupService();
            try {
                $goods = Db::table('order_goods')->where('order_id', $sNo)->first();
                if (!$goods) {
                    return $this->rejson(201, '没有该订单数据');
                }
                if ($orders->puzzle_id == 0 || $orders->puzzle_id != $puzzle_id || $orders->is_del == 1) {
                    return $this->rejson(201, '非法订单');
                }
                $num = $goods->num;
                $service->openOrJoinGroup($orders->id, $puzzle_id, $open_join, $num, $all['uid'], $group_id);
            } catch (Exception $e) {
                if ($e->getCode() == 201) {
                    return $this->rejson(201, '拼团失败,' . $e->getMessage());
                }
                return $this->rejson(500, '未知错误,拼团失败');
            }
        }

        $data['user_id'] = $all['uid'];
        $data['describe'] = '商城购物消费';
        $data['create_time'] = date('Y-m-d H:i:s', time());
        $data['type_id'] = 2;
        $data['price'] = $orders->order_money - $integral;
        $data['state'] = 2;
        $data['is_del'] = 0;
        $status['status'] = 20;
        $status['pay_money'] = $orders->order_money - $integral;
        $status['pay_way'] = $all['pay_id'];
        $status['pay_time'] = date('Y-m-d H:i:s', time());

        DB::beginTransaction(); //开启事务
        $re = DB::table('user_logs')->insert($data);
        $ress = DB::table('orders')->where('order_sn', $sNo)->update($status);
        $ress = DB::table('order_goods')->where('order_id', $sNo)->update($status);
        $res = DB::table('users')->where('id', $all['uid'])->decrement('money', $data['price']);
        if ($integral > 0) {
            $addintegral = $data;
            $addintegral['price'] = $integral;
            $addintegral['type_id'] = 1;
            $rei = DB::table('user_logs')->insert($addintegral);
            $resi = DB::table('users')->where('id', $all['uid'])->decrement('integral', $integral);
        }
        if ($res && $re && $ress) {
            DB::commit();
            return $this->rejson(200, '支付成功');
        } else {
            DB::rollback();
            return $this->rejson(201, '支付失败');
        }

    }

    public function wxPay() {
        $all = request()->all();
        if (empty($all['sNo'])) {
            return $this->rejson(201, '参数错误');
        }

        $sNo = $all['sNo'];

        $orders = DB::table('orders')
            ->where('order_sn', $sNo)
            ->first();
        if (empty($orders)) {
            return $this->rejson(201, '订单不存在');
        }
        $order_goods = DB::table('order_goods')->where('order_id', $sNo)->get();
        if (isset($all['is_integral']) && $all['is_integral'] == 1) {
            $allintegral = DB::table('users')->where('id', $all['uid'])->first()->integral;
            $integrals = DB::table('config')->where('key', 'integral')->first()->value;
            $integral = floor(($orders->order_money - $orders->shipping_free) * $integrals);
            if ($allintegral < $integral) {
                return $this->rejson(201, '积分不足');
            } else {
                foreach ($order_goods as $key => $value) {
                    $uporder['integral'] = floor(($value->pay_money - $value->shipping_free) * $integrals);
                    $uporder['pay_money'] = $value->pay_money - $uporder['integral'];
                    DB::table('order_goods')->where('id', $value->id)->update($uporder);
                }
                $dataintegral['integral'] = $integral;
                DB::table('orders')->where('order_sn', $sNo)->update($dataintegral);
            }
        } else {
            $integral = 0;
        }

        // hcq新增：团购支付处理
        $puzzle_id = empty($all['puzzle_id']) ? 0 : $all['puzzle_id'];
        $open_join = empty($all['open_join']) ? 0 : $all['open_join'];
        $group_id = empty($all['group_id']) ? 0 : $all['group_id'];
        if ($orders->puzzle_id != 0) {
            if (empty($puzzle_id)) {
                return $this->rejson(201, '团购参数错误');
            }
            $service = new GroupService();
            try {
                $goods = Db::table('order_goods')->where('order_id', $sNo)->first();
                if (!$goods) {
                    return $this->rejson(201, '没有该订单数据');
                }
                if ($orders->puzzle_id == 0 || $orders->puzzle_id != $puzzle_id || $orders->is_del == 1) {
                    return $this->rejson(201, '非法订单');
                }
                $num = $goods->num;
                $service->openOrJoinGroup($orders->id, $puzzle_id, $open_join, $num, $all['uid'], $group_id);
            } catch (Exception $e) {
                if ($e->getCode() == 201) {
                    return $this->rejson(201, '拼团失败,' . $e->getMessage());
                }
                return $this->rejson(500, '未知错误,拼团失败');
            }
        }

        $pay_money = 100 * ($orders->order_money - $integral);

        $order = WeChatPay::getInstance()->createOrder(
            $sNo,
            $pay_money,
            '安抖本地生活-购物',
            '购物订单',
            request()->ip(),
            Carbon::now()->addHour()->format('YmdHis')
        );
        // var_dump($order);exit();
        if ($order) {
            return $this->rejson(200, '获取支付信息成功！', $order);
        }
        return $this->rejson(201, '获取支付信息失败！');
    }

    public function aliPay() {
        $all = request()->all();
        if (empty($all['sNo'])) {
            return $this->rejson(201, '参数错误');
        }

        $sNo = $all['sNo'];

        $orders = DB::table('orders')
            ->where('order_sn', $sNo)
            ->first();
        if (empty($orders)) {
            return $this->rejson(201, '订单不存在');
        }
        $order_goods = DB::table('order_goods')->where('order_id', $sNo)->get();
        if (isset($all['is_integral']) && $all['is_integral'] == 1) {
            $allintegral = DB::table('users')->where('id', $all['uid'])->first()->integral;
            $integrals = DB::table('config')->where('key', 'integral')->first()->value;
            $integral = floor(($orders->order_money - $orders->shipping_free) * $integrals);
            if ($allintegral < $integral) {
                return $this->rejson(201, '积分不足');
            } else {
                foreach ($order_goods as $key => $value) {
                    $uporder['integral'] = floor(($value->pay_money - $value->shipping_free) * $integrals);
                    $uporder['pay_money'] = $value->pay_money - $uporder['integral'];
                    DB::table('order_goods')->where('id', $value->id)->update($uporder);
                }
                $dataintegral['integral'] = $integral;
                DB::table('orders')->where('order_sn', $sNo)->update($dataintegral);
            }
        } else {
            $integral = 0;
        }

        // hcq新增：团购支付处理
        $puzzle_id = empty($all['puzzle_id']) ? 0 : $all['puzzle_id'];
        $open_join = empty($all['open_join']) ? 0 : $all['open_join'];
        $group_id = empty($all['group_id']) ? 0 : $all['group_id'];
        if ($orders->puzzle_id != 0) {
            if (empty($puzzle_id)) {
                return $this->rejson(201, '团购参数错误');
            }
            $service = new GroupService();
            try {
                $goods = Db::table('order_goods')->where('order_id', $sNo)->first();
                if (!$goods) {
                    return $this->rejson(201, '没有该订单数据');
                }
                if ($orders->puzzle_id == 0 || $orders->puzzle_id != $puzzle_id || $orders->is_del == 1) {
                    return $this->rejson(201, '非法订单');
                }
                $num = $goods->num;
                $service->openOrJoinGroup($orders->id, $puzzle_id, $open_join, $num, $all['uid'], $group_id);
            } catch (Exception $e) {
                if ($e->getCode() == 201) {
                    return $this->rejson(201, '拼团失败,' . $e->getMessage());
                }
                return $this->rejson(500, '未知错误,拼团失败');
            }
        }

        $pay_money = $orders->order_money - $integral;

        $orderStr = Alipay::getInstance()->createOrder(
            $sNo,
            '安抖本地生活-消费',
            '饭店预定',
            $pay_money,
            Carbon::now()->addHour()->format('Y-m-d H:i'),
            0
        );
        if ($orderStr) {
            return $this->rejson(200, '获取支付信息成功！', ['orderstr' => $orderStr]);
        }
        return $this->rejson(201, '获取支付信息失败！');
    }

    /**
     * @api {post} /api/order/addcomment 添加商品评论
     * @apiName addcomment
     * @apiGroup order
     * @apiParam {string} uid 用户id（必填）
     * @apiParam {string} token 用户验证（必填）
     * @apiParam {string} goods_id 商品id（必填）
     * @apiParam {string} order_id 订单号（必填）
     * @apiParam {string} merchants_id 商户id（必填）
     * @apiParam {string} content 评价内容（非必填）
     * @apiParam {string} stars 评价星级（必填）
     * @apiParam {string} image 商品图片（非必填）
     * @apiParam {Number} [vote=0]
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "msg":"查询成功",
     *       "data": "",
     *     }
     */
    public function addcomment() {
        $all = request()->all();
        if (!isset($all['uid']) ||
            !isset($all['token']) ||
            !isset($all['stars']) ||
            !isset($all['goods_id']) ||
            !isset($all['order_id']) ||
            !isset($all['merchants_id'])) {
            return $this->rejson(201, '缺少参数');
        }
        $check = $this->checktoten($all['uid'], $all['token']);
        if ($check['code'] == 201) {
            return $this->rejson($check['code'], $check['msg']);
        }
        if (!empty($all['image'])) {
            $image = json_encode($all['image']);
        } else {
            $image = '';
        }
        if (!empty($all['content'])) {
            $content = $all['content'];
        } else {
            $content = '此用户没有评论任何内容';
        }
        $data = [
            'user_id' => $all['uid'],
            'order_id' => $all['order_id'],
            'goods_id' => $all['goods_id'],
            'merchants_id' => $all['merchants_id'],
            'content' => $content,
            'stars' => $all['stars'],
            'image' => $image,
            'created_at' => date('Y-m-d H:i:s'),
            'type' => 2,
        ];
        $status['status'] = 60;
        DB::table('orders')->where('order_sn', $all['order_id'])->update($status);
        DB::table('order_goods')->where('order_id', $all['order_id'])->update($status);
        if (isset($all['vote']) && $all['vote'] == 1) {
            DB::table('merchants')->find($all['merchants_id'])->increment('praise_num');
        }
        $i = DB::table('order_commnets')->insert($data);
        if ($i) {
            return $this->rejson(200, '添加成功');
        } else {
            return $this->rejson(201, '添加失败');
        }
    }

    /**
     * @api {post} /api/order/confirm 确认收货
     * @apiName confirm
     * @apiGroup order
     * @apiParam {string} uid 用户id（必填）
     * @apiParam {string} token 用户验证（必填）
     * @apiParam {string} id 子订单id
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "msg":"收货成功",
     *       "data": "",
     *     }
     */
    public function confirm() {
        $all = request()->all();
        if (empty($all['id'])) {
            return $this->rejson(201, '缺少参数');
        }
        $re = DB::table('order_goods')->where(['id' => $all['id'], 'status' => 40])->first();
        if (empty($re)) {
            return $this->rejson(201, '订单不存在');
        }
        $name = DB::table('goods')->where('id', $re->goods_id)->first()->name ?? '';
        $log['price'] = $re->total;
        $order_sn = $re->order_id;
        $log['msg'] = $name . '出售成功,订单编号：' . $re->order_id;
        $log['type'] = 2;
        $log['status'] = 1;
        $log['created'] = date('Y-m-d H:i:s', time());
        $log['user_id'] = $re->user_id;
        $log['merchant_id'] = $re->merchant_id;
        DB::beginTransaction(); //开启事务
        $res = DB::table('merchant_log')->insert($log);
        $ress = DB::table('merchants')->where('id', $re->merchant_id)->increment('money', $log['price']);
        $ordre = DB::table('order_goods')->where('id', $all['id'])->update(['status' => 50]);
        $re = DB::table('order_goods')->where(['id' => $all['id'], 'status' => 40])->first();
        if (empty($re)) {
            $ordres = DB::table('orders')->where('order_sn', $order_sn)->update(['status' => 50]);
        } else {
            $ordres = 1;
        }
        if ($res && $ress && $ordres && $ordre) {
            DB::commit();
            return $this->rejson(200, '收货成功');
        } else {
            DB::rollback();
            return $this->rejson(201, '收货失败');
        }
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Exception
     *
     * @api {post} /api/order/cancel 取消订单
     * @apiName cancel
     * @apiGroup order
     * @apiParam {Number} uid
     * @apiParam {String} order_sn
     * @apiParam {Number} reason_id
     * @apiParam {String} [reason]
     * @apiSuccessExample Success-Response:
     * {}
     */
    public function cancel(Request $request) {
        $data = $this->validate($request, [
            'uid' => 'required|numeric|exists:users,id',
            'order_sn' => 'required|string|exists:orders,order_sn',
            'reason_id' => 'required|numeric|exists:order_cancel_reason,id',
            'reason' => 'nullable|string|max:255'
        ]);
        $order = Orders::getInstance()->where('user_id', $data['uid'])
            ->where('order_sn', $data['order_sn'])->first();
        if ($order) {
            switch ($order->status) {
                case 0:
                    return $this->responseJson(201, '订单已取消');
                case 10:
                    if (!in_array((int)$data['reason_id'], [1, 2, 3, 4])) {
                        return $this->responseJson(201, '无效原因信息');
                    }
                    break;
                default:
                    if (in_array((int)$data['reason_id'], [1, 2, 3, 4])) {
                        return $this->responseJson(201, '无效原因信息');
                    }

                    do {
                        try {
                            $refund_no = app('Snowflake\Snowflake')->next();
                        } catch (Exception $e) {
                            $refund_no = null;
                        }
                    } while (!$refund_no);

                    $reason = OrderCancelReason::getInstance()->find($data['reason_id'])->value('reason');

                    // 退款流程
                    switch ($order->pay_way) {
                        case 1:
                            if (!WeChatPay::getInstance()->refundOrder(
                                $refund_no,
                                $order->order_money * 100,
                                $order->order_money * 100,
                                $order->order_sn,
                                null,
                                $reason ?? null
                            )) {
                                return $this->responseJson(201, '退款失败');
                            }
                            break;
                        case 2:
                            // Alipay
                        case 3:
                            // UnionPay
                        case 4:
                            // Balance Pay
                            if (!Users::getInstance()->find($order->user_id)
                                ->increment('money', $order->order_money)) {
                                return $this->responseJson(201, '退款失败');
                            }
                    }
            }

            $order->status = 0;
            $order->updated_at = Carbon::now()->toDateTimeString();

            DB::beginTransaction();
            if ($order->save()) {
                $orderGood = OrderGoods::getInstance()->where('order_id', $data['order_sn']);

                // 还原销量
                $goodIds = $orderGood->pluck('goods_id');
                foreach ($goodIds as $goodId) {
                    Goods::getInstance()->find($goodId)->decrement('volume');
                }

                $updateRet = $orderGood->update(['status' => 0, 'updated_at' => $order->updated_at]);

                if ($updateRet != false && OrderCancel::getInstance()->insert([
                        'order_id' => $order->id,
                        'reason_id' => $data['reason_id'],
                        'reason' => $data['reason'] ?? ''
                    ])) {
                    DB::commit();
                    return $this->responseJson(200, '取消成功');
                }
            }
            DB::rollBack();
            return $this->responseJson(201, '取消失败');
        }
        return $this->responseJson(201, '订单不存在');
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @api {post} /api/order/refresh_addr 重选地址
     * @apiName refresh_addr
     * @apiGroup order
     * @apiParam {Number} uid
     * @apiParam {String} order_sn
     * @apiParam {Number} addr_id
     * @apiSuccessExample Success-Response:
     * {}
     */
    public function refreshAddr(Request $request) {
        $data = $this->validate($request, [
            'uid' => 'required|numeric|exists:users,id',
            'order_sn' => 'required|string|exists:orders,order_sn',
            'addr_id' => 'required|numeric|exists:user_address,id'
        ]);

        $order = Orders::getInstance()
            ->where('user_id', $data['uid'])
            ->where('order_sn', $data['order_sn'])
            ->where('status', 10) // 只能修改未支付订单
            ->first();

        if ($order) {
            $addr = UserAddress::getInstance()
                ->where('user_id', $data['uid'])
                ->where('status', 1)
                ->find($data['addr_id'])
                ->exists();
            if ($addr) {
                if($order->update(['address_id' => $data['addr_id']])){
                    return $this->responseJson(200, '更新地址成功');
                }
                return $this->responseJson(201,'更新地址失败');
            }
            return $this->responseJson(201, '用户地址信息不存在');
        }

        return $this->responseJson(201, '订单不存在');
    }
}
