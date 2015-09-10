angular.module('myapp')
.factory('authInterceptor',['$rootScope', '$location', 'jwtHelper', 'Auth', '$timeout', '$q', '$injector',
function($rootScope, $location, jwtHelper, Auth, $timeout, $q, $injector)
{
    var obj = {}
    var loginModal, $http, $state, cfpLoadingBar;

    // this trick must be done so that we don't receive
    // `Uncaught Error: [$injector:cdep] Circular dependency found`
    $timeout(function () {
        loginModal = $injector.get('loginModal');
        $http = $injector.get('$http');
        $state = $injector.get('$state');
    });

    //obj.request = function(config){
    //    config.headers = config.headers || {};
    //    var token = Auth.getToken();
    //    //if( token ){
    //    //    var endAt = jwtHelper.getTokenExpirationDate(token);
    //    //    if( endAt.valueOf() - new Date().valueOf() < 59*60*1000 ){
    //    //        $http.get('auth/refresh_jwt').then(
    //    //            function(response){
    //    //                console.log('new token:' + response.data.token);
    //    //            },
    //    //            function(error){
    //    //                console.log('error:' + error);
    //    //            }
    //    //        );
    //    //        console.log("token 即將逾期...." + '' );
    //    //    }
    //    //    console.log("token 有效時間剩下...." + (endAt.valueOf() - new Date().valueOf()) );
    //    //
    //    //}
    //    if( token && config.url.substr(config.url.length - 5) !== '.html' ){
    //        config.headers.Authorization = 'Bearer ' + token;
    //    }
    //
    //    return config;
    //};

    obj.response = function(response){
        //取得登入後附加在 url 的 token 或 之後由 api 回傳之 token
        var token = $location.search()['token'] || response.data.token;

        if( token !== undefined ) {
            Auth.saveToken( token );
            console.log('token saved...');
            if(response.data.token_type !== undefined && response.data.token_type === 'refresh_profile'){
                // fresh token
                // 停留在原畫面
                $rootScope.$broadcast('profile_update_success');
                //$location.url($location.path());
            }else if(response.data.token_type !== 'refresh_only'){
                // user logined
                // 導向至 dashboard，可藉此去除 url 之 token，再依據 user role 導向
                $rootScope.$broadcast('UserLogined');
                //$location.url($location.path());
                $location.url('/dashboard');
            }
        }

        return response;
    }

    //obj.responseError = function(response){
    //    if( response.status == 400 || response.status == 401 || response.status == 403 || response.status == 404 ) {
    //        Auth.logoutUser();
    //        $location.url('/login');
    //        console.log('status = ' + response.status);
    //    }
    //
    //    return response;
    //}

    obj.responseError = function (rejection) {
        console.log('response error:');
        console.log(rejection);

        if(rejection.data[0] === 'token_invalid'){
            Auth.deleteToken();
        }
        //if (rejection.status !== 400 && rejection.status !== 401 && rejection.status !== 403 && rejection.status !== 404) {
        //    return rejection;
        //}
        if (rejection.status !== 400 && rejection.status !== 401) {

            return rejection;
        }

        var deferred = $q.defer();

        loginModal()
            .then(function () {
                deferred.resolve( $http(rejection.config) );
            })
            .catch(function () {
                $state.go('index');
                deferred.reject(rejection);
            });

        return deferred.promise;
    }

    return obj;
}]);