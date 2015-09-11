angular.module('myapp')
.controller('ApplicationController', ['$rootScope', 'Auth', 'growl', '$state', '$location', 'Backend',
function($rootScope, Auth, growl, $state, $location, Backend)
{
    var vm = this;

    // 若已登入則取得 user 資料
    if( $rootScope.logined = Auth.isAuthenticated() ) {
        setCurrentUser();
    }

    // 監聽：UserLogined
    $rootScope.$on('UserLogined', function(){
        $rootScope.logined = true;
        setCurrentUser();
        growl.success('登入成功');
    });

    // 監聽：profile_update_success
    $rootScope.$on('profile_update_success', function(){
        setCurrentUser();
        //growl.success('個人資料更新完成');
    });

    // 監聽：UserLogouted
    $rootScope.$on('UserLogouted', function(){
        clearData();
        growl.success('您已成功登出');
    });

    // 監聽：token_expired
    $rootScope.$on('token_expired', function(){
        clearData();
        growl.warning('憑證已逾期，請重新登入');
    });

    // 監聽：token_invalid
    $rootScope.$on('token_invalid', function(){
        clearData();
        growl.error('憑證錯誤，請重新登入');
    });

    // 監聽：token_not_provided
    $rootScope.$on('token_not_provided', function(){
        clearData();
        growl.error('缺少憑證，請先登入');
    });

    // 監聽：permission_deny
    $rootScope.$on('permission_deny', function(){
        growl.error('權限不足');
    });

    // 監聽：no_any_role
    $rootScope.$on('no_any_role', function(){
        growl.error('未設定角色，請聯絡網站管理員',{ttl: 10000, disableCountDown: false});
    });

    // 監聽：user_not_found
    $rootScope.$on('user_not_found', function(){
        clearData();
    });

    // 取得 user 資料
    function setCurrentUser(){
        $rootScope.me = Auth.getCurrentUserFromToken();
    }

    // 清除資料
    function clearData(){
        $rootScope.logined = false;
        $rootScope.me = undefined;
    }

}]);