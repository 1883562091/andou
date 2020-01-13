@extends('admin.layouts.layout')
@include('vendor.ueditor.assets')
<link href="{{loadEdition('/admin/plugins/layui/css/layui.css')}}">
<script src="{{loadEdition('/admin/plugins/layui/layui.all.js')}}"></script>
<link rel="stylesheet" href="{{loadEdition('/assets/plugins/bootstrap/css/bootstrap.min.css')}}">
<link rel="stylesheet" href="{{loadEdition('/assets/css/font-awesome.min.css')}}">
<link rel="stylesheet" href="{{loadEdition('/assets/css/animate.css')}}">
<link rel="stylesheet" href="{{loadEdition('/assets/css/main.css')}}">

<script src="{{loadEdition('/assets/js/jquery.min.js')}}"></script>
<script src="https://cdn.bootcss.com/webuploader/0.1.1/webuploader.js"></script>
<script src="{{loadEdition('/assets/plugins/bootstrap/js/bootstrap.min.js')}}"></script>
<script src="{{loadEdition('/assets/plugins/waypoints/waypoints.min.js')}}"></script>
<script src="{{loadEdition('/assets/js/application.js')}}"></script>
<script src="{{loadEdition('/assets/plugins/wizard/js/loader.min.js')}}"></script>
<script src="{{loadEdition('/assets/plugins/wizard/js/jquery.form.js')}}"></script>
<script src="{{loadEdition('/assets/js/modernizr-2.6.2.min.js')}}"></script>
<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.bootcss.com/layer/2.3/layer.js"></script>
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox-title">
                <h5>会员plus中心</h5>
            </div>
            <div class="ibox-content">
                <div class="hr-line-dashed m-t-sm m-b-sm"></div>
                <form class="form-horizontal m-t-md" action="{{route('member.indexChange')}}" method="post" accept-charset="UTF-8">
                    {!! csrf_field() !!}
                    <input type="hidden" name="id" value="{{ $data -> id or '' }}" />
                    <input type="hidden" name="config_data_id" value="{{ $config_data -> id or '' }}" />
                    <div class="form-group">
                        <label class="col-sm-2 control-label">购买会员所需金额：</label>
                        <div class="input-group col-sm-2">
                            <input type="text" class="form-control" name="value" value="{{$config_data -> value or '100'}}" required placeholder="请输入购买会员所需金额">
                        </div>
                    </div>
                    <script type="text/css">
                    </script>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">会员权益：</label>
                        <div class="col-sm-6" style="height: 500px !important;">
                            <script id="container" name="desc" type="text/plain">{!!$data ->content or ''!!}</script>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-12 col-sm-offset-2">
                            <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i>&nbsp;保 存</button>　<button class="btn btn-white" type="reset"><i class="fa fa-repeat"></i> 重 置</button>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </form>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        var ue = UE.getEditor('container',{
            initialFrameWidth:null ,//宽度随浏览器自适应
            wordCount: false, //关闭字数统计
            elementPathEnabled : false,//隐藏元素路径
            autoHeightEnabled: false,//是否自动长高
            autoFloatEnabled: false//是否保持toolbar的位置不动
        });
        ue.ready(function() {
            ue.setHeight(250);
            ue.execCommand('serverparam', '_token', '{{ csrf_token() }}'); // 设置 CSRF token.
        });
    </script>

@endsection
