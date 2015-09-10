angular.module('myapp')
.controller('LoginController', ['$scope', 'isAuthenticated', '$state', 'Auth',
function($scope, isAuthenticated, $state, Auth)
{
    // 已驗證則導向
    if(isAuthenticated) {
        $state.go('auth.dashboard');
        return;
    }

    var vm = this;

    //vm.backtoStateName = $state.current.name;

    vm.loginLocalUser = function(user){
        // 先關閉 login modal，否則若登入失敗會在上層產生新的 login modal
        $scope.$dismiss();
        Auth.loginLocalUser(user).then(function (data) {
            //console.log(data);
            console.info('user 已登入...');
        });
    };
}]);