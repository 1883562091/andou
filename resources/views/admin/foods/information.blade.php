@extends('admin.layouts.layout')
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox-title">
                <h5>商户菜品详情</h5>
            </div>
            <div class="ibox-content">
                {{-- 按条件查询 --}}
                {{--<form method="post" action="{{route('merchants.index')}}" name="form">--}}
                {{--{{ csrf_field() }}--}}
                <a class="menuid btn btn-primary btn-sm" href="javascript:history.go(-1)">返回</a>&nbsp;
                {{--判断用户是否是超级管理员，超级管理员不能新增菜品--}}
                {{--@if($id)--}}
                    <a href="{{route('foods.informationadd')}}" link-url="javascript:void(0)">
                        <button class="btn btn-primary btn-sm" type="button">
                            <i class="fa fa-plus-circle"></i> 新增菜品</button>
                    </a>
                {{--@endif--}}
                {{--<input type="text" style="height: 25px;margin-left: 10px;" name="name" placeholder="菜品名字">--}}
                    {{--<select style="height: 25px;margin-left: 10px;" name="merchant_type_id">--}}
                        {{--<option value="0">菜品分类</option>--}}
                    {{--</select>--}}
                    {{--<button style="height: 25px;margin-left: 10px;" type="submit">按条件查询</button>--}}
                {{--</form>--}}
                    <style>
                        th ,td{
                            text-align: center;
                        }
                    </style>
                    <table class="table table-striped table-bordered table-hover m-t-md">
                        <thead>
                        <tr>
                            <th width="100">ID</th>
                            <th>商户ID</th>
                            <th>分类ID</th>
                            <th style="width: 150px;">菜品名称</th>
                            <th>菜品价格</th>
                            <th>菜品图片</th>
                            <th>菜品规格</th>
                            <th style="width: 200px;">菜品介绍</th>
                            <th>每月销售数量</th>
                            <th>点赞</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(count($data) > 0)
                                @foreach($data as $v)
                                    <tr>
                                        <th>{{ $v->id }}</th>
                                        <th>{{ $v->merchant_id }}</th>
                                        <th>{{ $v->classification_id }}</th>
                                        <th><p style="width: 150px;overflow: hidden;white-space: nowrap;text-overflow: ellipsis;">{{ $v->name }}</p></th>
                                        <th>{{ $v->price }}￥</th>
                                        <td>
                                            <img src="{{ $v->image }}" style="width: 80px;height: 80px">
                                        </td>
                                        <th>{{ $v->specifications }}</th>
                                        <th><p style="width: 200px;overflow: hidden;white-space: nowrap;text-overflow: ellipsis;">{{ $v->remark }}</p></th>
                                        <th>{{ $v->quantitySold }}</th>
                                        <th>{{ $v->num }}</th>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="{{route('foods.informationadd')}}?id={{$v->id}}"><button class="btn btn-primary btn-xs" type="button"><i class="fa fa-paste"></i> 修改</button></a>
                                                <a onclick="del({{$v->id}})"><button class="btn btn-danger btn-xs" type="button"><i class="fa fa-ban"></i> 删除</button></a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                            <tr>
                                <th colspan="9">暂时还没有数据</th>
                            </tr>
                        @endif
                        </tbody>
                    </table>

            </div>
        </div>
        <div class="clearfix"></div>
    </div>

    <script type="text/javascript">
        function del(e) {
            var id = e;
            layer.alert("是否删除该数据？",{icon:3},function (index) {
                location.href="{{route('foods.informationdel')}}?id="+id;
                layer.close(index);
            });
        }
    </script>

@endsection