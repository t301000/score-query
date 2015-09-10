<!DOCTYPE html>
<html lang="zh-TW" ng-app="myapp" ng-controller="ApplicationController as root">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>歷次段考成績查詢</title>

    <link href="{{ asset('build/css/bundle.min.css') }}" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <!--<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>-->
      <!--<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>-->
    <![endif]-->
  </head>
  <body>
  	<nav class="navbar navbar-inverse navbar-fixed-top ng-cloak" ng-controller="navController as nav">
  	  <div class="container">
  	    <!-- Brand and toggle get grouped for better mobile display -->
  	    <div class="navbar-header">
  	      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse-1" ng-click="nav.isCollapsed = !nav.isCollapsed">
  	        <span class="sr-only">Toggle navigation</span>
  	        <span class="icon-bar"></span>
  	        <span class="icon-bar"></span>
  	        <span class="icon-bar"></span>
  	      </button>
  	      <a class="navbar-brand" ui-sref="index"><i class="icon ion-android-home"></i></a>
  	    </div>

  	    <!-- Collect the nav links, forms, and other content for toggling -->
  	    <div class="collapse navbar-collapse" id="navbar-collapse-1" collapse="!nav.isCollapsed">
  	      <ul class="nav navbar-nav" ng-if="$root.logined">
  	        <li ui-sref-active="active" ng-show="$root.me.roles.indexOf('admin') !== -1"><a ui-sref="auth.admin">管理區</a></li>
  	        <li ui-sref-active="active" ng-show="$root.me.roles.indexOf('teacher') !== -1"><a ui-sref="auth.teacher">教師作業區</a></li>
  	        <li ui-sref-active="active" ng-show="$root.me.roles.indexOf('parents') !== -1"><a ui-sref="auth.parents">成績查詢區</a></li>
  	      </ul>
  	      <ul class="nav navbar-nav navbar-right">
  	        <li ng-if="!$root.logined"><a href="" ng-click="nav.showLoginModal()"><i class="icon ion-log-in"></i> 登入</a></li>
  	        <li ng-if="$root.logined" class="dropdown" dropdown>
  	          <a href="#" class="dropdown-toggle" dropdown-toggle data-toggle="dropdown" role="button" aria-expanded="false">@{{ $root.me.real_name }} <span class="caret"></span></a>
  	          <ul class="dropdown-menu" role="menu">
  	            <li><a href="" ng-click="nav.editProfileModal()"><i class="icon ion-person"></i>&nbsp;&nbsp;我的資料</a></li>
  	            <li class="divider"></li>
  	            <li><a href="" ng-click="nav.logout()"><i class="icon ion-log-out"></i>&nbsp;&nbsp;登出</a></li>
  	          </ul>
  	        </li>
  	      </ul>
  	    </div><!-- /.navbar-collapse -->
  	  </div><!-- /.container -->
  	</nav>

    <div class="container">
      <div class="row">
        <ui-view></ui-view>
      </div>
    </div>

	<!-- for 提示訊息 -->
	<div growl></div>

    <script src="{{ asset('build/js/bundle.min.js') }}"></script>
    <script src="{{ asset('build/js/myapp-all.min.js') }}"></script>

  </body>
</html>