<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
class UsersController extends Controller
{   
    public function __construct()
    {
        $all=request()->all();
        if (empty($all['uid'])||empty($all['token'])) {
           return $this->rejson(201,'登陆失效');
        }
        $check=$this->checktoten($all['uid'],$all['token']);
        if ($check['code']==201) {
           return $this->rejson($check['code'],$check['msg']);
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
                {
                    "id": "商户id",
                    "created_at": "创建时间",
                    "stars_all": "星级",
                    "praise_num":"点赞数量"
                    "logo_img":"商家图片",
                    "name":"商家名字"
                }
            ],     
     *       "msg":"查询成功"
     *     }
     */
    public function merchantRecord(){
        $all=request()->all();
        $num=10;
        $start=0;
        if (!empty($all['page'])) {
            $page=$all['page'];
            $start=$num*($page-1);
        }
        $data=Db::table('see_log as c')
        ->join('merchants as m','m.id','=','c.pid')
        ->where(['c.user_id'=>$all['uid'],'c.type'=>2])
        ->select('m.id','m.address','m.tel','m.stars_all','m.praise_num','m.name','m.logo_img')
        ->orderBy('c.id',"DESC")
        ->offset($start)
        ->limit($num)
        ->get();
        return $this->rejson(200,'查询成功',$data);
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
    public function fabulous(){
        $all=request()->all();
        if (empty($all['id'])) {
            return $this->rejson(201,'缺少参数');
        }
        $data['user_id']=$all['uid'];
        $data['pid']=$all['id'];
        $data['created_at']=date('Y-m-d H:i:s',time());
        $datas=Db::table('fabulous')->where(['user_id'=>$all['uid'],'pid'=>$all['id']])->first();
    
        if (empty($datas)) {
            $re=Db::table('fabulous')->insert($data);
            $res=DB::table('merchants')->where('id',$all['id'])->increment('praise_num');
            return $this->rejson(200,'点赞成功');
        }else{
            return $this->rejson(201,'不能重复点赞');
        }   
    }
}