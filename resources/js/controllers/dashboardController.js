angular.module('myapp')
.controller('DashboardController', ['$state', '$rootScope',
function($state, $rootScope)
{
    if($rootScope.me.roles.length === 0){
        $rootScope.$broadcast('no_any_role');
        return $state.go('index');
    }

    if($rootScope.me.roles.indexOf('admin')=== -1){
        return $state.go('auth.'+$rootScope.me.roles[0]);
    }else{
        return $state.go('auth.admin');
    }

}]);