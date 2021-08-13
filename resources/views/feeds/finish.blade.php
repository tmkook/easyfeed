@extends('layouts.feed')

@section('content')

<ol class="breadcrumb">
    <li class="breadcrumb-item">提交站点</li>
    <li class="breadcrumb-item">验证站点</li>
    <li class="breadcrumb-item active">保存完成</li>
</ol>



<div class="d-grid gap-4 d-sm-flex justify-content-sm-center my-5">

    @if($success)
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" width="72" height="72" class="bi bi-check-circle" viewBox="0 0 16 16" style="color:#198754">
            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
            <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
        </svg>
    @else
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" width="72" height="72" class="bi bi-info-circle" viewBox="0 0 16 16" style="color:#dc3545">
            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
            <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
        </svg>
    @endif
    <div>
        <h2>{{$title}}</h2>
        <p class="lead mb-4">{{$info}}</p>
    </div>
</div>

<div class="my-4 text-center">
    <a href="/" class="btn btn-primary">完成</a>
</div>

@endsection