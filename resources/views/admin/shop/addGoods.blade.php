@extends('admin.layouts.layout')
@include('vendor.ueditor.assets')
<link rel="stylesheet" href="{{loadEdition('/assets/plugins/bootstrap/css/bootstrap.min.css')}}">
<link rel="stylesheet" href="{{loadEdition('/assets/css/font-awesome.min.css')}}">
<link rel="stylesheet" href="{{loadEdition('/assets/css/animate.css')}}">
<link rel="stylesheet" href="{{loadEdition('/assets/css/main.css')}}">
<link href="{{loadEdition('/admin/plugins/layui/css/layui.css')}}">

<script src="{{loadEdition('/assets/js/jquery.min.js')}}"></script>
<script src="{{loadEdition('/admin/plugins/layui/layui.all.js')}}"></script>
<script src="https://cdn.bootcss.com/webuploader/0.1.1/webuploader.js"></script>
<script src="{{loadEdition('/assets/plugins/bootstrap/js/bootstrap.min.js')}}"></script>
<script src="{{loadEdition('/assets/plugins/waypoints/waypoints.min.js')}}"></script>
<script src="{{loadEdition('/assets/js/application.js')}}"></script>
<script src="{{loadEdition('/assets/plugins/wizard/js/loader.min.js')}}"></script>
<script src="{{loadEdition('/assets/plugins/wizard/js/jquery.form.js')}}"></script>
<script src="{{loadEdition('/assets/js/modernizr-2.6.2.min.js')}}"></script>
<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.bootcss.com/layer/2.3/layer.js"></script>
<style>
    .add_div {
        width: 700px;
        height: 500px;
        border: solid #ccc 1px;
        margin-top: 24px;
        padding-left: 20px;
    }

    .file-list {
        height: 125px;
        display: none;
        list-style-type: none;
    }

    .file-list img {
        width: 150px;
        height: 170px;
        vertical-align: middle;
        font-size: 12px;
    }

    .file-list .file-item {
        width: 150px;
        height: 207px;
        margin-bottom: 10px;
        float: left;
        margin-left: 20px;
    }

    .file-list .file-item .file-del {
        display: block;
        text-align: center;
        margin-top: 5px;
        cursor: pointer;
        font-size: 12px;
    }


</style>
@section('content')
    <section>
        <section id="main-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">添加商品</h3>
                            <div class="actions pull-right">
                                <i class="fa fa-chevron-down"></i>
                                <i class="fa fa-times"></i>
                            </div>
                        </div>
                        <a class="menuid btn btn-primary btn-sm" href="javascript:history.go(-1)">返回</a>
                        <a class="menuid btn btn-primary btn-sm" onclick="reloadPage()">刷新</a>
                        <script>
                            function reloadPage() {
                                location.reload();
                            }
                        </script>
                        <div class="panel-body">
                            <section class="fuelux">
                                <div id="MyWizard" class="wizard">
                                    <ul class="steps">
                                        <li data-target="#step1" class="active">
                                            <span class="badge badge-info">1</span>基本信息
                                            <span class="chevron"></span>
                                        </li>
                                        <li data-target="#step2">
                                            <span class="badge">2</span>添加参数
                                            <span class="chevron"></span>
                                        </li>
                                    </ul>
                                    <div class="actions">
                                        <button type="button" class="btn btn-default btn-mini btn-prev"><i
                                                class="fa fa-chevron-left"></i>上一步
                                        </button>
                                        <button type="button" class="btn btn-primary btn-mini btn-next" id="next"
                                                data-last="Finish">下一步 <i class="fa fa-chevron-right"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="step-content">
                                    <div class="step-pane active" id="step1">
                                        <form class="form-horizontal" action="{{ route('shop.store') }}" method="post"
                                              id='addGoods' accept-charset="UTF-8" enctype="multipart/form-data">
                                            {{csrf_field()}}
                                            {{method_field('POST')}}
                                            <input type="hidden" name="goods_id" value="{{ $goods_id ?? ''}}"/>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><em
                                                        style="margin-right:5px;vertical-align: middle;color: #fe0000;">*</em>商品类目：</label>
                                                <div class="col-sm-2">
                                                    <select class="form-control pull-left" id="level1"
                                                            name="goods_cate_id" data-level="1">
                                                        <option value=""></option>
                                                        @foreach($goodsCate as $item)
                                                            <option value="{{$item->id}}"
                                                                    @if(isset($cates[0]) && $cates[0] == $item->id) selected @endif>{{$item->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-sm-2">
                                                    <select class="form-control col-sm-2 pull-left" id="level2"
                                                            name="goods_cate_id1" data-level="2">
                                                    </select>
                                                    <input type="hidden" id="cate2"
                                                           value="{{isset($cates[1]) ? $cates[1] : 0}}">
                                                </div>
                                                <div class="col-sm-2">
                                                    <select class="form-control col-sm-2 pull-left" id="level3"
                                                            name="goods_cate_id2" data-level="3"></select>
                                                    <input type="hidden" id="cate3"
                                                           value="{{isset($cates[2]) ? $cates[2] : 0}}">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><em
                                                        style="margin-right:5px;vertical-align: middle;color: #fe0000;">*</em>产品名称：</label>
                                                <div class="col-sm-6">
                                                    <input type="text" name="name" value="{{ $goodsdata->name ?? '' }}"
                                                           class="form-control">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-sm-2 control-label">
                                                    <em style="margin-right:5px;vertical-align: middle;color: #fe0000;">*</em>产品分类：
                                                </label>
                                                <div class="col-sm-2">
                                                    <select class="form-control pull-left" name="merchants_goods_type">
                                                        @foreach($merchants_goods_type as $item)
                                                            <option value="{{$item->id}}"
                                                                    @if($item->id == $goodsdata->merchants_goods_type_id) selected @endif > {{$item->name}}</option>
                                                        @endforeach
                                                    </select>
                                                    <a href="{{route('shop.merchants_goods_typeChange')}}"
                                                       link-url="javascript:void(0)">
                                                        <button class="btn btn-outline btn-link btn-sm" type="button">
                                                            设置分类
                                                        </button>
                                                    </a>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><em
                                                        style="margin-right:5px;vertical-align: middle;color: #fe0000;">*</em>产品参数：</label>
                                                <div class="col-sm-2">
                                                    <select class="form-control pull-left" name="merchants_goods_type">
                                                        <option value="0">— 选择参数模板 —</option>
                                                        {{--@foreach($goods_attr as $item)--}}
                                                        {{--<option value="{{$item->id}}" @if($item->id == $goodsdata->merchants_goods_type_id) selected @endif > {{$item->name}}</option>--}}
                                                        {{--@endforeach--}}
                                                    </select>
                                                    <div
                                                        style="width: 766px;min-height: 100px;margin-top: 36px;background-color: #EEEEEE;">
                                                        {{--@foreach($attrvalueData as $v)--}}
                                                        {{--<div style="display: inline-block;float: left;width: 100px;height: 40px;">--}}
                                                        {{--<input type="text" value="{{$v -> spec}}" readonly style="margin: 15px;background-color: #EEEEEE;border: 0px" />--}}
                                                        {{--</div>--}}
                                                        {{--<div style="display: inline-block;float: left;width: 650px;height: 40px;">--}}
                                                        {{--@foreach(json_decode($v->spec_value,true) as $m)--}}
                                                        {{--<label>--}}
                                                        {{--<input type="checkbox" />--}}
                                                        {{--</label>--}}
                                                        {{--@endforeach--}}
                                                        {{--</div>--}}
                                                        {{--@endforeach--}}
                                                    </div>
                                                    <div
                                                        style="width: 766px;min-height: 120px;margin-top: 10px;border: 1px solid #EEEEEE"></div>
                                                    {{--@foreach($attrData as $k =>$v)--}}
                                                    {{--<div class="form-group">--}}
                                                    {{--<label class="col-sm-2 control-label">--}}
                                                    {{--<input type="hidden" name="attrname[]" value="{{ $v -> id }}"  />--}}
                                                    {{--<input type="text"  value="{{ $v -> name }}" readonly style="border: 0px;width: 50px;" />--}}
                                                    {{--</label>--}}
                                                    {{--<div class="input-group col-sm-2">--}}
                                                    {{--<div class="checkbox i-checks checkbox">--}}
                                                    {{--@foreach($attrvalueData as $m)--}}
                                                    {{--@if($v -> id == $m -> goods_attr_id)--}}
                                                    {{--<span><label><input type="checkbox" name="attrvalue_{{ $v -> id }}[]" value="{{$m -> value}}" @if(in_array($m -> value,$goodssku)) checked @endif disabled />{{$m -> value}} </label></span>--}}
                                                    {{--@endif--}}
                                                    {{--@endforeach--}}
                                                    {{--</div>--}}
                                                    {{--</div>--}}
                                                    {{--</div>--}}
                                                    {{--@endforeach--}}
                                                </div>
                                            </div>

                                            <div class="form-group" method="post" id="addAlbum">
                                                <label class="col-sm-2 control-label"><em
                                                        style="margin-right:5px;vertical-align: middle;color: #fe0000;">*</em>封面图片：</label>
                                                <div class="layui-upload">
                                                    <button type="button" class="layui-btn" id="image"
                                                            style="display: none;">上传图片
                                                    </button>
                                                    <div class="layui-upload-list">
                                                        @if(empty($goodsdata -> img))
                                                            <input type="hidden" name="img" id="img" value=""/>
                                                            <img class="layui-upload-img" id="showImage"
                                                                 src="{{loadEdition('/admin/images/default_image.jpg')}}"
                                                                 style="width:300px;height:300px;">
                                                        @else
                                                            <input type="hidden" name="img" id="img"
                                                                   value="{{ $goodsdata->img }}"/>
                                                            <img class="layui-upload-img"
                                                                 src="{{ $goodsdata->img ?? loadEdition('/admin/images/default_image.jpg') }}"
                                                                 id="showImage" style="width:300px;height:300px;">
                                                        @endif
                                                        <p id="demoText"></p>
                                                    </div>
                                                </div>
                                                {{--上传图片--}}
                                                <script>
                                                    layui.use('upload', function () {
                                                        var $ = layui.jquery
                                                            , upload = layui.upload;
                                                        // 图片上传
                                                        var uploadInst = upload.render({
                                                            elem: '#image'
                                                            ,
                                                            url: '/admin/upload/uploadImage'
                                                            ,
                                                            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
                                                            ,
                                                            before: function (obj) {
                                                                // 预读本地文件示例
                                                                obj.preview(function (index, file, result) {
                                                                    $('#showImage').attr('src', result); //图片链接（base64）
                                                                });
                                                            }
                                                            ,
                                                            done: function (res) {
                                                                //如果上传失败
                                                                if (res.code != 200) {
                                                                    return layer.msg('上传失败');

                                                                }
                                                                $('#img').val(res.path);
                                                            }
                                                            ,
                                                            error: function () {
                                                                //演示失败状态，并实现重传
                                                                var demoText = $('#demoText');
                                                                demoText.html('<span style="color: #FF5722;">上传失败</span> <a class="layui-btn layui-btn-xs demo-reload">重试</a>');
                                                                demoText.find('.demo-reload').on('click', function () {
                                                                    uploadInst.upload();
                                                                });
                                                            }
                                                        });
                                                    });
                                                </script>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><em
                                                        style="margin-right:5px;vertical-align: middle;color: #fe0000;">*</em>详情图片：</label>
                                                <div class="col-sm-6 add_div">
                                                    <p><input type="file" name="choose-file[]" id="choose-file"
                                                              multiple="multiple"/>
                                                    </p>
                                                    <p>
                                                    @if(!empty($goodsdata->album))
                                                        <ul class="file-list image_ul " style="display: block;">
                                                            @foreach($goods_album as $v)
                                                                <li style="border:1px gray solid; margin:5px 5px;"
                                                                    class="file-item">
                                                                    <input type="hidden" name="choose_file[]"
                                                                           value="{{ $v }}"/>
                                                                    <img src="{{ $v }}" alt="" height="70">
                                                                    <span class="btn btn-danger file-del">删除</span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <ul class="file-list image_ul " style="display: block;"></ul>
                                                        @endif
                                                        </p>
                                                </div>
                                            </div>
                                            <div class="form-group" style="height:400px;">
                                                <label class="col-sm-2 control-label"><em
                                                        style="margin-right:5px;vertical-align: middle;color: #fe0000;">*</em>产品详情：</label>
                                                <div class="col-sm-6">
                                                    <script id="container" name="desc"
                                                            type="text/plain">{!!$goodsdata->desc ?? ''!!}</script>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><em
                                                        style="margin-right:5px;vertical-align: middle;color: #fe0000;">*</em>基础价格：</label>
                                                <div class="input-group col-sm-2">
                                                    <input type="text" class="form-control" name="price"
                                                           value="{{ $goodsdata->price ?? '' }}" required
                                                           data-msg-required="请输入商品价格">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><em
                                                        style="margin-right:5px;vertical-align: middle;color: #fe0000;">*</em>产品重量：</label>
                                                <div class="input-group col-sm-2">
                                                    <input type="text" class="form-control" name="weight"
                                                           value="{{ $goodsdata->weight ?? '' }}" required
                                                           data-msg-required="请输入商品重量"><span style="font-size: 12px">KG 产品重量应用于按重量计费的快递方式，一般以1kg为首重（或起重），每增加1kg为续重。</span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><em
                                                        style="margin-right:5px;vertical-align: middle;color: #fe0000;">*</em>上架设置：</label>
                                                <div class="input-group col-sm-2">
                                                    <div class="radio i-checks">
                                                        {{--is_sale--}}
                                                        <label><input type="radio" name='is_sale'
                                                                      value="1" {{$goodsdata -> is_sale == 1 ? "checked" : ''}}/>上架</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                        <label><input type="radio" name='is_sale'
                                                                      value="0" {{$goodsdata -> is_sale == 0 ? "checked" : ''}}/>暂不上架</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-2 control-label"><em
                                                        style="margin-right:5px;vertical-align: middle;color: #fe0000;">*</em>运费方式：</label>
                                                <div class="input-group col-sm-2">
                                                    <div class="radio i-checks">
                                                        <select name="dilivery">
                                                            <option value="0"
                                                                    @if($goodsdata->dilivery == 0) selected @endif>包邮
                                                            </option>
                                                            @foreach($express_modeldata as $v)
                                                                <option value="{{ $v -> id }}"
                                                                        @if($v -> id == $goodsdata -> dilivery) selected @endif >{{ $v -> name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <a href="{{route('shop.express')}}"
                                                           link-url="javascript:void(0)">
                                                            <button class="btn btn-outline btn-link btn-sm"
                                                                    type="button">设置运费
                                                            </button>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="hr-line-dashed"></div>
                                            <div class="form-group">
                                                <div class="col-sm-12 col-sm-offset-2">
                                                    <button class="btn btn-primary" type="submit"><i
                                                            class="fa fa-check"></i>&nbsp;保 存
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="hr-line-dashed"></div>
                                        </form>
                                    </div>
                                    <div class="step-pane" id="step2">
                                        <form class="form-horizontal" action="{{ route('shop.storeComplateAttrs') }}"
                                              method="get" id='storeComplateAttrs' accept-charset="UTF-8"
                                              enctype="multipart/form-data">
                                            {!! csrf_field() !!}
                                            <input type="hidden" name="goods_id" value="{{ $goods_id ?? ''}}"/>
                                            <div class="form-group">
                                                <div class="col-sm-2">
                                                    <h2 class="title">添加商品参数</h2>
                                                </div>
                                            </div>
                                            @foreach($attrData as $k =>$v)
                                                <div class="hr-line-dashed"></div>
                                                <div class="form-group">
                                                    <label class="col-sm-2 control-label">
                                                        <input type="hidden" name="attrname[]" value="{{ $v -> id }}"/>
                                                        <input type="text" value="{{ $v -> name }}" readonly
                                                               style="border: 0px;width: 50px;"/>
                                                    </label>
                                                    <div class="input-group col-sm-2">
                                                        <div class="radio i-checks checkbox">
                                                            @foreach($attrvalueData as $m)
                                                                @if($v->id == $m->goods_attr_id)
                                                                    <label><input type="checkbox"
                                                                                  name="attrvalue_{{ $v->id }}[]"
                                                                                  value="{{$m->value}}"
                                                                                  @if(in_array($m -> value,$goodssku)) checked @endif />{{$m -> value}}
                                                                    </label>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            <div class="hr-line-dashed"></div>
                                            <div class="form-group">
                                                <div class="col-sm-12 col-sm-offset-2">
                                                    <button class="btn btn-primary" type="submit"><i
                                                            class="fa fa-check"></i>&nbsp;保 存
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="hr-line-dashed"></div>
                                        </form>
                                    </div>
                                </div>
                            </section>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </section>

    <script type="text/javascript">
        var ue = UE.getEditor('container', {
            initialFrameWidth: null,//宽度随浏览器自适应
            wordCount: false, //关闭字数统计
            elementPathEnabled: false,//隐藏元素路径
            autoHeightEnabled: false,//是否自动长高
            autoFloatEnabled: false//是否保持toolbar的位置不动
        });
        ue.ready(function () {
            ue.setHeight(250);
            ue.execCommand('serverparam', '_token', '{{ csrf_token() }}'); // 设置 CSRF token.
        });
    </script>

    <script>

        $(function () {
            // Init
            var selected = $('#level1').val();
            if (selected) {
                var cate2 = $('#cate2').val();
                loadCategories(selected, 2)
                loadCategories(cate2, 3);
            }

            function loadCategories(current_id, level) {
                $.ajax({
                    url: '/admin/shop/getCateChildren',
                    type: 'post',
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    data: {id: current_id},
                    dataType: 'json',
                    async: false,
                    success: function (response, status) {
                        if (status === 'success') {
                            var eleValue = $('#cate' + level);
                            if (eleValue.length > 0) {
                                var cate = eleValue.val();
                                eleValue.remove();
                            } else {
                                var cate = 0;
                            }
                            var html = '';
                            for (var i in response.data) {
                                if (cate == response.data[i].id) {
                                    html += '<option value="' + response.data[i].id + '" selected>' + response.data[i].name + '</option>';
                                } else {
                                    html += '<option value="' + response.data[i].id + '">' + response.data[i].name + '</option>';
                                }
                            }
                            $('#level' + level).html(html);
                        }
                    }
                });
            }

            $('.step-content #addGoods select[data-level]').change(function () {
                var currentLevel = parseInt($(this).attr('data-level'));
                if (currentLevel == 1) {
                    $('#level3').html('');
                }
                loadCategories($(this).val(), currentLevel + 1);
            });
        });

        $(function () {
            ////////////////////////////////////////////////图片上传//////////////////////////////////////////////
            //声明变量
            var $button = $('#upload'),
                //选择文件按钮
                $file = $("#choose-file"),
                //回显的列表
                $list = $('.file-list'),
                //选择要上传的所有文件
                fileList = [];
            //当前选择上传的文件
            var curFile;
            $file.on('change', function (e) {
                //上传过图片后再次上传时限值数量
                var numold = $('.image_ul li').length;
                if (numold >= 6) {
                    layer.alert('最多上传6张图片');
                    return;
                }
                //限制单次批量上传的数量
                var num = e.target.files.length;
                var numall = numold + num;
                if (num > 6) {
                    layer.alert('最多上传6张图片');
                    return;
                } else if (numall > 6) {
                    layer.alert('最多上传6张图片');
                    return;
                }
                //原生的文件对象，相当于$file.get(0).files;//files[0]为第一张图片的信息;
                curFile = this.files;
                //将FileList对象变成数组
                fileList = fileList.concat(Array.from(curFile));
                //console.log(fileList);
                for (var i = 0, len = curFile.length; i < len; i++) {
                    reviewFile(curFile[i])
                }
                $('.file-list').fadeIn(1000);
            })


            function reviewFile(file) {
                //实例化fileReader,
                var fd = new FileReader();
                //获取当前选择文件的类型
                var fileType = file.type;
                //调它的readAsDataURL并把原生File对象传给它，
                fd.readAsDataURL(file);//base64
                //监听它的onload事件，load完读取的结果就在它的result属性里了
                fd.onload = function () {
                    if (/^image\/[jpeg|png|jpg|gif]/.test(fileType)) {
                        $list.append('<li style="border:1px gray solid; margin:5px 5px;" class="file-item"><img src="' + this.result + '" alt="" height="70"><span class="btn btn-danger file-del">删除</span></li>').children(':last').hide().fadeIn(1000);
                    } else {
                        $list.append('<li class="file-item"><span class="file-name">' + file.name + '</span><span class="btn btn-danger file-del" style="font-size: 12px;">删除</span></li>')
                    }

                }
            }

            //点击删除按钮事件：
            $(".file-list").on('click', '.file-del', function () {
                let $parent = $(this).parent();
                console.log($parent);
                let index = $parent.index();
                fileList.splice(index, 1);
                $parent.fadeOut(850, function () {
                    $parent.remove()
                });
                //$parent.remove()
            });

            $("#imageSub").on('click', function () {

                if (fileList.length > 6) {
                    layer.alert('最多允许上传6张图片');
                    return;
                } else {
                    var form = document.getElementById("imageSubForm");
                    var formData = new FormData(form);
                    for (var i = 0, len = fileList.length; i < len; i++) {
                        //console.log(fileList[i]);
                        formData.append('choose-file[]', fileList[i]);
                    }
                    $.ajax({
                        url: "{{ route('shop.storeAlbum') }}",
                        type: 'post',
                        data: formData,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        success: function (data) {
                            if (data == 1) {
                                layer.alert("图片上传成功,即将跳转下一步...", {icon: 1}, function (index) {
                                    $("#next").click();
                                    layer.close(index);
                                });
                            } else {
                                layer.alert(data, {icon: 2});
                            }
                        },
                        error: function (e) {

                            layer.alert(e.responseText, {icon: 2});
                        }
                    })
                }
            })

        })

    </script>

@endsection


