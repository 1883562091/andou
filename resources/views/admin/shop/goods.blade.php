@extends('admin.layouts.layout')
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#home" data-toggle="tab">产品列表</a></li>
                <li><a href="#profile" data-toggle="tab">产品分类</a></li>
            </ul>
            <div class="tab-content" style="z-index: auto">
                {{--产品列表--}}
                <div class="ibox-content tab-pane active a" id="home">
                    <form action="{{route('shop.goods')}}" method="get">
                        {!! csrf_field() !!}
                        <a class="menuid btn btn-primary btn-sm" href="javascript:history.go(-1)">返回</a>

                        <input type="text" style="height: 25px;margin-left: 10px;" value="{{ $product_name or '' }}" name="product_name" placeholder="请输入产品名称">
                        <button class="btn btn-primary btn-sm" type="submit"><i class="fa fa-search"></i> 查询</button>

                        <button type="button" class="btn btn-danger btn-sm mdels" title="批量删除" ><i class="fa fa-trash-o"></i> 批量删除</button>
                        <a href="{{route('shop.create')}}" link-url="javascript:void(0)"><button class="btn btn-primary btn-sm" type="button">
                                <i class="fa fa-plus-circle"></i> 新增商品</button>
                        </a>
                        <a href="{{url('/admin/shop/goods?status=1')}}" link-url="javascript:void(0)"><button class="btn btn-primary btn-sm" type="button">
                                上架</button>
                        </a>
                        <a href="{{url('/admin/shop/goods?status=2')}}" link-url="javascript:void(0)"><button class="btn btn-primary btn-sm" type="button">
                                下架</button>
                        </a>
                        <select name="sort" id="sort" style="width: 100px;height: 25px;">
                            <option value="0">排序</option>
                            <option value="1" @if($sort == 1) selected @endif>销量</option>
                            <option value="2" @if($sort == 2) selected @endif>价格</option>
                        </select>&nbsp;&nbsp;&nbsp;
                        提交时间:<input type="date"  style="height: 25px;margin-left: 10px;" class="one_time" name="one_time" placeholder="请选择时间">&nbsp;&nbsp;-
                        <input type="date"  style="height: 25px;margin-left: 10px;" class="two_time" name="two_time" placeholder="请选择时间">
                        <button class="btn btn-primary btn-sm" type="submit" >
                            <i class="fa fa-search"></i> 搜索
                        </button>
                    </form>

                    <form method="post" action="{{route('shop.index')}}" name="form">
                        <style>
                            th{
                                text-align: center;
                            }
                            #home td{
                                text-align: center;
                            }
                        </style>
                        <table class="table table-striped table-bordered table-hover m-t-md">
                            <thead>
                            <tr>
                                <th><input type="checkbox" id="checkall" /></th>
                                <th style="width: 250px">产品名称</th>
                                <th width="200px">产品类目</th>
                                <th>产品图片</th>
                                <th>是否上架</th>
                                <th>库存</th>
                                <th>销量</th>
                                <th>基础价格</th>
                                <th width="200px">上架时间</th>
                                {{--<th>更新时间</th>--}}
                                <th width="250px">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(count($list) > 0)
                            @foreach($list as $k => $item)
                                <tr>
                                    <td><input type="checkbox" name="ids" value="{{$item->id}}" /></td>
                                    <td><p style="width: 250px;overflow: hidden;white-space: nowrap;text-overflow: ellipsis;">{{$item->goods_name}}</p></td>
                                    <td>{{$item->goods_cate_id}}</td>
                                    <td><img src="{{ env('IMAGE_PATH_PREFIX')}}{{$item->img}}" alt="" style="width: 55px;height: 55px;"></td>
                                    <td>
                                        @if ($item->is_sale == 1)
                                            <span class="text-info">是</span>
                                        @else
                                            <span class="text-danger">否</span>
                                        @endif
                                    </td>
                                    <td>
                                        @foreach($goods_sku as $v)
                                            @if(in_array($item -> id,$v))
                                                {{ $v['total'] }}
                                            @endif
                                        @endforeach
                                    </td>
                                    <td>{{$item->volume}}</td>
                                    <td>{{$item->price}}</td>
                                    <td>{{$item->created_at}}</td>
                                    {{--<td>{{$item->updated_at}}</td>--}}
                                    <td class="text-center">
                                        <div class="btn-group">
                                            @if($item->is_sale == 0)
                                                <a href="{{route('shop.setStatus',['field'=>'is_sale','is_sale'=>1,'id'=>$item->id])}}"><button class="btn btn-outline btn-info btn-xs" type="button"><i class="fa fa-warning"></i> 上架</button></a>
                                            @else
                                                <a href="{{route('shop.setStatus',['field'=>'is_sale','is_sale'=>0,'id'=>$item->id])}}"><button class="btn btn-outline btn-warning btn-xs" type="button"><i class="fa fa-warning"></i>下架</button></a>
                                            @endif
                                            <a href="{{route('shop.update')}}?id={{$item->id}}">
                                                <button class="btn btn-outline btn-primary btn-xs" type="button"><i class="fa fa-paste"></i> 编辑</button>
                                            </a>
                                            <a onclick="del({{$item->id}})"><button class="btn btn-danger btn-xs" type="button"><i class="fa fa-trash-o"></i> 删除</button></a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                                @else
                                <tr>
                                    <td colspan="10">对不起未查询到相关数据</td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                        @if(count($list)>0)
                            {{ $list->appends(['status'=>$status]) }}
                        @endif
                    </form>
                </div>
                {{--产品分类--}}
                <div class="ibox-content tab-pane" id="profile">
                    <a class="menuid btn btn-primary btn-sm" href="javascript:history.go(-1)">返回</a>
                    <button type="button" class="btn btn-danger btn-sm mdelse" title="批量删除" ><i class="fa fa-trash-o"></i> 批量删除</button>
                    <a href="{{route('shop.merchants_goods_typeChange')}}" link-url="javascript:void(0)"><button class="btn btn-primary btn-sm" type="button">
                            <i class="fa fa-plus-circle"></i> 新增分类</button>
                    </a>

                    <form method="post" action="{{route('shop.express')}}" name="form">
                        <table class="table table-striped table-bordered table-hover m-t-md">
                            <thead>
                            <tr>
                                <th width="50px"><input type="checkbox" id="checkalls" /></th>
                                <th width="150px">分类ID</th>
                                <th width="250px">商家名称</th>
                                <th>分类名称</th>
                                <th width="200px">产品数</th>
                                <th width="200px">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(count($data) > 0)
                                @foreach($data as $k => $item)
                                    <tr>
                                        <td><input type="checkbox" name="idse" value="{{$item->id}}" /></td>
                                        <td>{{$item->id}}</td>
                                        <td>{{$item->merchants_name}}</td>
                                        <td>{{$item->name}}</td>
                                        <td>{{$item->num}}</td>
                                        <td>
                                            <a href="{{route('shop.merchants_goods_typeChange')}}?id={{$item->id}}">
                                                <button class="btn btn-primary btn-xs" type="button"><i class="fa fa-paste"></i> 编辑</button>
                                            </a>
                                            <a onclick="del_class({{$item->id}})"><button class="btn btn-danger btn-xs" type="button"><i class="fa fa-trash-o"></i> 删除</button></a>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <th colspan="6">暂时没有查询到数据</th>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
    <script src="{{loadEdition('/js/jquery.min.js')}}"></script>
    <script type="text/javascript">
        // 排序
        $("#sort").change(function () {
            var options = $("#sort option:selected").val();
            location.href="{{route('shop.sort')}}?id="+options;
        });

        function del(e) {
            var id = e;
            layer.alert("是否删除该数据？",{icon:3},function (index) {
                location.href="{{route('shop.goodsDel')}}?id="+id;
                layer.close(index);
            });
        }
        //执行批量删除商品
        $(".mdels").click(function () {
            var obj = document.getElementsByName("ids");
            var check_val = [];
            for(k in obj){
                if(obj[k].checked)
                    check_val.push(obj[k].value);
            }
            if(check_val==""){
                layer.alert("请选择你需要删除的选项",{icon:2});
            }else {
                layer.confirm("是否删除这 "+check_val.length+" 项数据？", {icon: 3}, function (index) {
                    $.post("{{route('shop.deleteAll')}}", {ids: check_val, _token: "{{csrf_token()}}"}, function (data) {
                        if (data = 1) {
                            layer.alert("删除成功", {icon: 1}, function (index) {
                                window.location.href = "{{route('shop.goods')}}";
                            });
                        }
                    })

                })
            }
        })
        // 实现全选
        $("#checkall").click(function () {
            if(this.checked){
                $("[name=ids]:checkbox").prop("checked",true);
            }else{
                $("[name=ids]:checkbox").prop("checked",false);
            }
        })

        /*
        *               商品分类
        *
        * */

        //执行批量删除分类
        $(".mdelse").click(function () {
            var obj = document.getElementsByName("idse");
            var check_val = [];
            for(k in obj){
                if(obj[k].checked)
                    check_val.push(obj[k].value);
            }
            if(check_val==""){
                layer.alert("请选择你需要删除的选项",{icon:2});
            }else {
                layer.confirm("是否删除这 "+check_val.length+" 项数据？", {icon: 3}, function (index) {
                    $.post("{{route('shop.goodsAlldel')}}", {ids: check_val, _token: "{{csrf_token()}}"}, function (data) {
                        if (data = 1) {
                            layer.alert("删除成功", {icon: 1}, function (index) {
                                window.location.href = "{{route('shop.goods')}}";
                            });
                        }
                    })

                })
            }
        })
        function del_class(e) {
            var id = e;
            layer.alert("是否删除该数据？",{icon:3},function (index) {
                location.href="{{route('shop.merchants_goods_typeDel')}}?id="+id;
                layer.close(index);
            });
        }
        // 实现全选
        $("#checkalls").click(function () {
            if(this.checked){
                $("[name=idse]:checkbox").prop("checked",true);
            }else{
                $("[name=idse]:checkbox").prop("checked",false);
            }
        })
    </script>
@endsection