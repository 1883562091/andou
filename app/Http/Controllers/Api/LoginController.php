<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Hash;
use Auth;
class LoginController extends Controller
{   
    /**
     * @api {post} /api/login/login_p 手机登陆
     * @apiName loginP
     * @apiGroup login
     * @apiParam {string} phone 手机号码
     * @apiParam {string} password 密码
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "data": {
     *           'name':'刘明',
     *           'login_count':'登陆次数',
     *           'mobile':'18883562091',
     *           'token':'登陆验证'
     *       },
     *       "msg":"登陆成功"
     *     }
     */
    public function loginP(){
        $all=request()->all();
        if (empty($all['phone']) || empty($all['password'])) {
            return $this->rejson(201,'参数错误');
        }else{
            $phone=$all['phone'];
        }
        if(!Auth::guard('admin')->attempt([
            'mobile'     => $phone,
            'password' => $all['password'],
        ])){
            return $this->rejson(201,'账号密码错误');
        }else{
            $data=Db::table('users')
            ->select('id','name','login_count','mobile')
            ->where('mobile',$phone)
            ->first();
            $token = $this->token($data->id);
            $datas['token']=$token['token'];
            $datas['login_count']=$data->login_count+1;
            DB::table('users')->where('mobile',$phone)->update($datas);
            $data->token = $token['noncestr'];
            return $this->rejson(200,'登陆成功',$data);
        }
        
    }
    /**
     * @api {post} /api/login/reg_p 手机注册
     * @apiName regP
     * @apiGroup login
     * @apiParam {string} phone 手机号码
     * @apiParam {string} password 密码
     * @apiParam {string} verify 验证码
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "data": {
     *           'name':'刘明',
     *           'login_count':'登陆次数',
     *           'mobile':'18883562091'
     *       },
     *       "msg":"登陆成功"
     *     }
     */
    public function regP(){
        $all=request()->all();
        if (empty($all['phone']) || empty($all['password'] || empty($all['verify']))) {
            return $this->rejson(201,'参数错误');
        }else{
            // if ($all['password'] != $all['password_two']) {
            //     return $this->rejson(201,'两次密码不一样');
            // }
            if ($all['verify'] != Redis::get($all['phone'])) {
                return $this->rejson(201,'验证码错误');
            }
            $re=Db::table('users')->where('mobile',$all['phone'])->first();
            if ($re) {
                return $this->rejson(201,'账户已存在');
            }
            $data['mobile']=$all['phone'];
            $data['password']=Hash::make($all['password']);
            $data['create_ip'] = request()->ip();
            $data['last_login_ip'] = request()->ip();
            $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s',time());
            $data['source']=0;
            $data['is_del']=0;
            $data['name']='用户:'.$all['phone'];
            $data['avator']='/uploads/images/avators/201911/29//1575020535_VGSxFj53YP.jpg';
            $re=Db::table('users')->insertGetId($data);
            if ($re) {
                $data=Db::table('users')
                ->select('id','name','mobile')
                ->where('mobile',$all['phone'])
                ->first();
                return $this->rejson(200,'注册成功',$data);
            }else{
                return $this->rejson(201,'注册失败');
            }
        }
    }
    /**
     * @api {post} /api/login/send 发送短信验证
     * @apiName send
     * @apiGroup login
     * @apiParam {string} phone 手机号码
     * @apiParam {string} type 用户注册为1其它为零
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "data": {
     *        
     *       },
     *       "msg":"登陆成功"
     *     }
     */
    public function send(){//发送短信验证
        $code=rand(100000,999999);
        $all=request()->all();
        $mobile=$all['phone'];
        $type=$all['type'];
        $pattern = "/^1[34578]\d{9}$/"; 
        $res_1=preg_match($pattern,$mobile);
        if (empty($mobile) || !$res_1) {
            return $this->rejson(201,'参数错误');
        }
        if (!empty($type) && $type == 1) {
            $re=Db::table('users')->where('mobile',$mobile)->first();
            if ($re) {
                return $this->rejson(201,'账户已存在');
            }            
        }
        $send = $this->sendmessage($code,$mobile);

        if ($send) {
            return $this->rejson(200,'发送手机验证成功');
        }else{
            return $this->rejson(201,'发送短信验证失败');
        }
         
    }
    /**
     * @api {post} /api/login/forget 忘记密码
     * @apiName forget
     * @apiGroup login
     * @apiParam {string} phone 手机号码
     * @apiParam {string} verify 验证码
     * @apiParam {string} new_password 验证码
     * @apiSuccessExample 参数返回:
     *     {
     *       "code": "200",
     *       "data": {
     *            "password":"新密码"
     *       },
     *       "msg":"登陆成功"
     *     }
     */
    public function forget(){
       $all=request()->all();
       if (empty($all['phone']) || empty($all['verify']) ||empty($all['new_password'])) {
            return $this->rejson(201,'参数错误');
       }
       $mobile=$all['phone'];
       if ($all['verify'] != Redis::get($all['phone'])) {
                return $this->rejson(201,'验证码错误');
        }
        $re=Db::table('users')->where('mobile',$mobile)->first();
        if (empty($re)) {

            return $this->rejson(201,'用户不存在');
        }

       $new_password=$all['new_password'];
       $datas['password']=Hash::make($new_password);
       $re=Db::table('users')->where('mobile',$mobile)->update($datas);
       return $this->rejson('200',"修改密码成功",array('password'=>$new_password));
    }
    /**
     * @api {post} /api/login/cache 获取短信测试
     * @apiName get_cache
     * @apiGroup login
     */
    public function cache(){
        echo $a=Redis::get('18883562091');
    }
}