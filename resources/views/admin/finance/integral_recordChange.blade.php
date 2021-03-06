@extends('admin.layouts.layout')
<link href="{{loadEdition('/admin/plugins/layui/css/layui.css')}}">
<script src="{{loadEdition('/admin/plugins/layui/layui.all.js')}}"></script>
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox-title">
                @if(empty($data->id))
                    <h5>新增感恩币明细</h5>
                    @else
                    <h5>修改感恩币明细</h5>
                @endif
            </div>
            <div class="ibox-content">
                <a class="menuid btn btn-primary btn-sm" href="javascript:history.go(-1)">返回</a>

                <div class="hr-line-dashed m-t-sm m-b-sm"></div>
                <form class="form-horizontal m-t-md" action="{{route('finance.integral_recordChange')}}" method="post" accept-charset="UTF-8">
                    {!! csrf_field() !!}
                    <input type="hidden" name="id" value="{{ $data->id or '' }}" />
                    <div class="form-group">
                        <label class="col-sm-2 control-label">类型描述：</label>
                        <div class="input-group col-sm-2">
                            <input type="text" class="form-control" name="describe" value="{{$data->describe or ''}}" required placeholder="类型描述">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">感恩币状态：</label>
                        <select  style="height: 25px;width: 273px;" name="state" >
                                <option value="1" >获得</option>
                                <option value="0" >消耗</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">感恩币值：</label>
                        <div class="input-group col-sm-2">
                            <input type="text" class="form-control" name="price" value="{{$data->price or ''}}" required placeholder="感恩币值">
                        </div>
                    </div>
                    <div class="hr-line-dashed m-t-sm m-b-sm"></div>
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

@endsection
