<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/12/30
 * Time: 14:59
 */

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
class DetailsController extends Controller
{
    /**
     * @api {post} /api/details/list 酒店商家详情
     * @apiName list
     * @apiGroup details
     * @apiParam {string} id 商家id
     * @apiParam {string} uid 用户id
     * @apiSuccessExample 参数返回：
     * {
     * "code":"200",
     * "data":{
     *             "door_img":"商家门头图",
     *             "stars_all":"商家星级",
     *             "address":"详细地址",
     *             "praise_num":"点赞数量",
     *             "name":"商家名称",
     *             "tel":"商家电话",
     *             "id":"商户id",
     *             "desc":"商家简介",
     *             "facilities":"商家环境设施",
     *             "status": "是否关注 1已关注 0未关注"
     *          },
     *  "msg":"查询成功"
     * }
     *
     */
    public function list()
    {
        $all = request()->all();
        if(empty($all['id'])){
            return $this->rejson(201,"缺少参数");
        }
        $data = DB::table("merchants")
            ->select(['id','name','tel','banner_img as door_img','stars_all','address','praise_num','desc','facilities'])
            ->where('id', $all['id'])
            ->first();
        $arr = DB::table('collection')->where('user_id',$all['uid'])->where('type',3)->where('pid',$all['id'])->first();
        if($arr){
            $data->status = 1;
        }else{
            $data->status = 0;
        }
        if ($data) {
            $data->facilities=json_decode($data->facilities,1)??[];
            return $this->rejson(200,'查询成功',$data);
        } else {
            return $this->rejson(201, '查询失败');
        }
    }
    /**
     * @api {post} /api/details/room_list 房间列表
     * @apiName room_list
     * @apiGroup details
     * @apiParam {int} merchant_id 商户id
     * @apiParam {int} page 分页页码page
     * @apiSuccessExample 返回参数：
     *     {
     *        "code":"200",
     *        "data":[
     *{
    "id":"房型id",
    "img":"图片",
    "house_name":"房间名称",
    "price":"价格",
    "name":"房间介绍"
     *}
     *         ],
     *          "msg":"查询成功"

     * }
     */
    public function room_list(){
        $all=request()->all();
        $num=10;
        if(isset($all['page'])){
            $pages=($all['page']-1)*$num;
        }else{
            $pages=0;
        }

        $data['hotel_room']=DB::table("hotel_room")
            ->select(['id','house_name','price','img','desc'])
            ->where('merchant_id',$all['merchant_id'])
            ->where('status',1)
            ->offset($pages)
            ->limit($num)
            ->get();

        foreach($data['hotel_room'] as $key=>$value ) {
            $res=explode(',',$value->desc);
            $data['hotel_room'][$key]->name='';
            $value->img=json_decode($value->img,true)??[];
            if (!empty($res)){
                foreach ($res as $k=>$v){
                    $data['hotel_room'][$key]->name.=DB::table('hotel_faci')
                            ->where('id',$v)
                            ->first()->name ?? '';
                    $data['hotel_room'][$key]->name.=',';
                }
            }
        }

        return $this->rejson(200,'查询成功',$data);
    }
    /**
     * @api {post} /api/details/hotelSel 房间详情
     * @apiName hotelSel
     * @apiGroup details
     * @apiParam {int} id 房间id
     * @apiSuccessExample 返回参数：
     *     {
     *       "code":"200",
     *       "data":{
     *                  "id":"房间id",
     *                  "img":"房间图片",
     *                  "price":"房间价格",
     *                  "house_name":"房间名称",
     *                  "areas":"面积",
     *                  "has_window":"窗户",
     *                  "wifi":"wifi",
     *                  "num_people":"可住人数",
     *                  "has_breakfast":"有无早餐",
     *                  "bed_type":"床型",
     *                  "other_sets":"配套设置"
     *               },
     *    "msg":"查询成功"
     *     }
     */
    public function hotelSel()
    {
        $all = request()->all();
        if(empty($all['id'])){
            return $this->rejson(201,"缺少参数");
        }

        $data = DB::table("hotel_room as r")
            ->join("hotel_attr_value as a","r.id","=","a.hotel_room_id")
            ->select(['r.id', 'r.img', 'r.price', 'r.house_name','a.areas','a.has_window','a.wifi','a.num_people','a.has_breakfast','a.bed_type','a.other_sets'])
            ->where('r.id', $all['id'])
            ->where('r.status',1)
            ->first();
        $data->img=json_decode($data->img,true)??[];
        return $this->rejson(200, '查询成功', $data);
    }
    /**
     * @api {post} /api/details/commnets 酒店住宿评论
     * @apiName commnets
     * @apiGroup details
     * @apiParam {string}  id 商户id
     * @apiParam {string}  page 分页页码
     * @apiSuccessExample 返回参数：
     * {
     *     "code":"200",
     *     "data":[
     *          {
     *          "stars":"评星",
     *          "created_at":"评论时间",
     *          "content":"评论内容",
     *          "name":"用户名",
     *          "avator"："用户头像"
     *          }
     *      ],
     *      "msg":"查询成功"
     *   }
     */

    //评论
    public function commnets()
    {
        $all = \request()->all();
        $num=10;
        if ($all['page']){
            $pages=($all['page']-1)*$num;
        }else{
            $pages=0;
        }
        $data = DB::table("order_commnets as m")
            ->join("users as u","m.user_id","=","u.id")
            ->select(['m.merchants_id','m.content','m.stars','m.created_at','u.avator','u.name'])
            ->where(['m.merchants_id'=>$all['id'],'m.type'=>1])
            ->offset($pages)
            ->limit($num)
            ->get();
        if ($data) {
            return $this->rejson(200, "查询成功", $data);
        } else {
            return $this->rejson(201, "查询失败");
        }
    }
    /**
     * @api {post} /api/details/addcomment 添加酒店评论
     * @apiName addcomment
     * @apiGroup details
     * @apiParam {string} uid 用户id（必填）
     * @apiParam {string} token 用户验证（必填）
     * @apiParam {string} goods_id 商品id（必填）
     * @apiParam {string} order_id 订单号（必填）
     * @apiParam {string} merchants_id 商户id（必填）
     * @apiParam {string} content 评价内容（非必填）
     * @apiParam {string} stars 评价星级（必填）
     * @apiParam {string} image 商品图片（非必填）
     * @apiParam {string} dianzhan 是否点赞(0未点赞 1点赞)
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "msg":"查询成功",
     *       "data": "",
     *     }
     */
    public function addcomment(){
        $all=request()->all();
        if (!isset($all['uid']) ||
            !isset($all['token']) ||
            !isset($all['stars']) ||
            !isset($all['goods_id']) ||
            !isset($all['order_id']) ||
            !isset($all['merchants_id']) ){
            return $this->rejson(201,'缺少参数');
        }
        $check=$this->checktoten($all['uid'],$all['token']);
        if ($check['code']==201) {
            return $this->rejson($check['code'],$check['msg']);
        }
        if(!empty($all['image'])){
            $image = json_encode($all['image']);
        }else{
            $image = '';
        }
        if(!empty($all['content'])){
            $content = $all['content'];
        }else{
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
            'type' => 1,
        ];

        if(empty($all['dianzhan']==1)){
            $da['user_id']=$all['uid'];
            $da['pid']=$all['id'];
            $da['created_at']=date('Y-m-d H:i:s',time());
            $re=DB::table('fabulous')->insert($da);
            $res=DB::table('merchants')->where('id',$all['id'])->increment('praise_num');

        }
        $status['status']=50;
        $re=DB::table('orders')->where('order_sn',$all['order_id'])->update($status);
        $res=DB::table('books')->where('book_sn',$all['order_id'])->update($status);

        $i = DB::table('order_commnets') -> insert($data);
        if($i){
            return $this->rejson(200,'添加成功');
        }else{
            return $this->rejson(201,'添加失败');
        }
    }

}
