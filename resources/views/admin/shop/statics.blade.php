@extends('admin.layouts.layout')
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox-title">
                <h5>商城统计</h5>
            </div>
            <div class="ibox-content">
                <a class="menuid btn btn-primary btn-sm" href="javascript:history.go(-1)">返回</a>
                    <style>
                        th ,td{
                            text-align: center;
                        }
                    </style>
                    <table class="table table-striped table-bordered table-hover m-t-md">
                        <thead>
                        <tr>
                            <th width="100">ID</th>
                            <th>用户</th>
                            <th>金额</th>
                            <th>描述</th>
                            <th>财务状况</th>
                            <th>流动时间</th>
                            <th>所属类型</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(count($data) > 0)
                        @foreach($data as $k=>$item)
                        <tr>
                            <td>{{$item->id}}</td>
                            <td>{{$item->name}}</td>
                            <td>{{$item->price}}</td>
                            <td>{{$item->describe}}</td>
                            <th style="color: green">{{ $item -> state == 1 ? "获得" : "消耗" }}</th>
                            <td>{{$item->create_time}}</td>
                            <td>
                                @if($item -> type_id == 1)
                                    感恩币流水
                                @elseif($$item -> type_id == 2)
                                    充值流水
                                @elseif($$item -> type_id == 3)
                                    提现流水
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a onclick="del({{$item->id}})"><button class="btn btn-danger btn-xs" type="button"><i class="fa fa-trash-o"></i> 删除</button></a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                            @else
                        <tr>
                            <td colspan="8">未查询到相关内容</td>
                        </tr>
                        @endif
                        </tbody>
                    </table>
                {{$data}}
                </form>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
    <script src="{{loadEdition('/js/jquery.min.js')}}"></script>
    <script>
        function del(e) {
            var id = e;
            layer.alert("是否删除该数据？",{icon:3},function (index) {
                location.href="{{route('shop.staticsDel')}}?id="+id;
                layer.close(index);
            });
        }
    </script>

@endsection