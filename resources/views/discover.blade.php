<!DOCTYPE HTML>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width,initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=0"/>
<meta name="apple-mobile-web-app-status-bar-style" content="white"/>
<meta name="apple-mobile-web-app-capable" content="yes"/>
<meta content="telephone=no" name="format-detection" />
<meta name="screen-orientation" content="portrait"/>
<meta name="renderer" content="webkit">
<title>uniblog</title>
<!-- <link rel="stylesheet" type="text/css" href=""> -->
<style>
body{background:#F5F5F5;}
html,body,div,p,h1,h2,h3,h4{padding:0;margin:0;font-size:16px;color:#333;}
a{color:#4b9ffc;text-decoration:none;}
a:hover,a:active{color:#1f2487;}
#app{width:100%;max-width:980px;position:relative;margin:0 auto;overflow:hidden;}
.newsbox .news{background:#FFF;border-radius:6px;margin-top:10px;padding:10px;}
.newsbox .news p{line-height:1.5em;margin-top:5px;}
.paginator{text-align:center;padding:10px;}
</style>
</head>

<body>
<div id="app">
    <div class="newsbox">
        @foreach($news as $item)
        <div class="news">
            <h3><a href="{{route('read',['uuid'=>$item->uuid])}}">{{$item->title}}</a></h3>
            <p>{!! $item->summary !!}...</p>
            <p>{{$item->feed->title}}</p>
        </div>
        @endforeach
    </div>
    <div class="paginator">
        <a href="{{$news->previousPageUrl()}}">上一页</a>
        <a href="{{$news->nextPageUrl()}}">下一页</a>
    </div>
</div>
</body>
</html>
