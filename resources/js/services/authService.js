angular.module('myapp')
.factory('Auth',['$rootScope', '$localStorage', 'jwtHelper', '$q', '$injector', '$timeout', 'MyAppConfig',
function($rootScope, $localStorage, jwtHelper, $q, $injector, $timeout, MyAppConfig)
{
    var obj = {};

    var $http;
    $timeout(function () {
        $http = $injector.get('$http');
    });


    //是否已驗證
    obj.isAuthenticated = function(){
        return obj.getToken() ? true : false;
    };

    // token 是否逾期
    obj.isTokenExpired = function(){
        var result;
        // 若逾期，則清除 token 並廣播
        if( result = jwtHelper.isTokenExpired( obj.getToken() ) ){
            $localStorage.$reset();
            $rootScope.$broadcast('token_expired');
        }
        return result;
    };

    // token 是否快要逾期？
    obj.willTokenExpired = function(token){
        var result;
        var timeToRefresh = MyAppConfig.TimeToRefreshToken; // 要更新token的時間(分鐘)

        var expiredAt = jwtHelper.getTokenExpirationDate(token);

        // 是否快逾期？
        result = !obj.isTokenExpired() && (expiredAt.valueOf() - new Date().valueOf() < timeToRefresh*60*1000);

        return result;
    }

    //從 local storage 取出 JWT token
    obj.getToken = function(){
        return $localStorage.myAuthToken;
    };

    // 取出 token payload
    obj.getTokenPayload = function(){
        return jwtHelper.decodeToken( obj.getToken() );
    }

    //JWT token 存入 local storage
    obj.saveToken = function( token ){
        //console.log(jwtHelper.decodeToken( token ));
        $localStorage.myAuthToken = token;
    };

    obj.deleteToken = function(){
        $localStorage.$reset();
    }

    // 由 token 取得目前登入 user 之資料
    obj.getCurrentUserFromToken = function(){
        var payload = obj.getTokenPayload();
        return {
            id:         payload.sub,
            real_name:  payload.real_name,
            provider:   payload.provider, // local、facebook、google、openid
            roles:      payload.roles  // 例如：['admin', 'teacher', 'parents]
        };
    };

    // 檢查是否有權限
    obj.hasPermission = function(requireRole){
        var userRoles = $rootScope.me.roles;
        var key, pass = false;

        for( key in requireRole ){
            if(userRoles.indexOf(requireRole[key]) !== -1){
                pass = true;
                break;
            }
        }

        return pass;
    };

    //登入 local user
    obj.loginLocalUser = function(user){
        //var $http = $injector.get("$http");
        return $http.post('/auth/local', user, { ignoreLoadingBar: true }).success(function(response){

            return response;
        });
    };

    //登出 user
    obj.logoutUser = function(){
        $localStorage.$reset();
        $rootScope.$broadcast('UserLogouted');
    };

    return obj;
}]);