@extends('layouts.feed')

@section('content')

<ol class="breadcrumb">
    <li class="breadcrumb-item">提交站点</li>
    <li class="breadcrumb-item active">验证站点</li>
    <li class="breadcrumb-item">保存完成</li>
</ol>

<form action="{{route('feed_created')}}" method="post">
    <div class="input-group col-12">
        <label class="input-group-text">站点</label>
        <input type="text" class="form-control" name="url" value="{{$url}}" readonly="true">
    </div>
    <div class="my-3"></div>
    <div class="input-group col-12">
        <label class="input-group-text">图标</label>
        <input type="text" class="form-control" name="logo" value="{{$logo}}" readonly="true">
    </div>
    <div class="my-3"></div>
    <div class="input-group col-12">
        <label class="input-group-text">标题</label>
        <input type="text" class="form-control" name="title" value="{{$title}}" readonly="true">
    </div>
    <div class="my-3"></div>
    <div class="input-group col-12">
        <label class="input-group-text">简介</label>
        <textarea class="form-control" name="description" rows="2" readonly="true">{{$description}}</textarea>
    </div>
    <div class="my-3"></div>
    <div class="input-group col-12">
        <label class="input-group-text">列表</label>
        <textarea class="form-control" rows="10" readonly="true">@json($list,JSON_PRETTY_PRINT)</textarea>
    </div>
    <div class="my-3"></div>
    <div class="input-group col-12">
        <label class="input-group-text">正文</label>
        <textarea class="form-control" rows="10" readonly="true">{{$content}}</textarea>
    </div>
    <div class="my-3"></div>
    <div class="input-group col-12">
        <button class="w-100 btn btn-primary btn-lg" type="submit">下一步</button>
    </div>
    {{csrf_field()}}
</form>
@endsection