@extends('admin.layouts.layout')
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox-title">
                <h5>优惠券管理</h5>
            </div>
            <div class="ibox-content">
                <a class="menuid btn btn-primary btn-sm" href="javascript:history.go(-1)">返回</a>
                <a href="{{route('coupon.list_change')}}" link-url="javascript:void(0)">
                    <button class="btn btn-primary btn-sm" type="button">
                        <i class="fa fa-plus-circle"></i> 新增优惠券</button>
                </a>
                    <style>
                        th ,td{
                            text-align: center;
                        }
                    </style>
                    <table class="table table-striped table-bordered table-hover m-t-md">
                        <thead>
                        <tr>
                            <th width="100">ID</th>
                            <th>优惠券名字</th>
                            <th>优惠券类型</th>
                            <th>货值</th>
                            <th>可发放总数量</th>
                            <th>发放剩余数量</th>
                            <th width="180">使用开始时间</th>
                            <th width="180">使用结束时间</th>
                            <th>状态</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($data as $k => $item)
                            <tr>
                                <td>{{$item->id}}</td>
                                <td>{{$item->coupon_name}}</td>
                                <th>@if($item->coupon_type_id==1)
                                        平台优惠券
                                    @else
                                        商户优惠券
                                    @endif</th>
                                <td>{{$item->money}}</td>
                                <td>{{$item->max_mun}}</td>
                                <td>{{$item->rest_num}}</td>
                                <td>{{$item->start_at}}</td>
                                <td>{{$item->end_at}}</td>
                                <td>@if($item->status==1)
                                        发放中
                                    @else
                                        未开启发放
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                    @if($item->status==1)
                                        <a href="{{route('coupon.delete')}}?id={{$item->id}}&status=0"><button class="btn btn-danger btn-xs" type="button"><i class="fa fa-trash-o"></i> 禁用</button></a>
                                    @else
                                        <a href="{{route('coupon.delete')}}?id={{$item->id}}&status=1"><button class="btn btn-group btn-xs" type="button"><i class="fa fa-check"></i> 启用</button></a>
                                    @endif
                                    <a href="{{route('coupon.list_change')}}?id={{$item->id}}"><button class="btn btn-primary btn-xs" type="button"><i class="fa fa-paste"></i> 修改</button></a>

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {{$data}}
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
@endsection