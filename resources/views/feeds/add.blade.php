@extends('layouts.feed')

@section('content')

<ol class="breadcrumb">
    <li class="breadcrumb-item active">提交站点</li>
    <li class="breadcrumb-item">验证站点</li>
    <li class="breadcrumb-item">保存完成</li>
</ol>

<form class="needs-validation" action="{{route('feed_verify')}}" method="post">
    <div class="col-12">
        <input type="text" class="form-control" name="url" placeholder="https://">
        <div class="invalid-feedback">
            请输入正确的URL
        </div>
    </div>
    <div class="my-4"></div>
    <div class="col-12">
        <button class="w-100 btn btn-primary btn-lg" type="submit">下一步</button>
    </div>
    {{csrf_field()}}
</form>
@endsection