angular.module('myapp')
.controller('navController', ['$rootScope', 'Auth', '$state', 'loginModal', '$modal', 'Restangular',
function($rootScope, Auth, $state, loginModal, $modal, Restangular)
{
    var vm = this;

    // 顯示登入 modal
    vm.showLoginModal = loginModal;

    // 顯示 profile modal
    vm.editProfileModal = function(){
        console.log($rootScope.me);
        var modalInstance = $modal.open({
            templateUrl: 'partials/modals/profile.html',
            controller: 'profileModalController',
            controllerAs: 'profile',
            size: 'sm',
            resolve: {
                // userData 為 restangular 物件
                userData: function () {
                    //return $rootScope.me;
                    return Restangular.one('users',$rootScope.me.id).get().then(function(data){return data;});
                }
            }
        });

        modalInstance.result.then(function (userData) {
            // userData 為回傳之修改後之 restangular 物件
            userData.mode = 'update_profile'; // 供後端判斷更新種類之用
            console.log(userData);
            userData.save();

        }, function () {
            console.log('Profile Modal dismissed at: ' + new Date());
        });
    };

    vm.logout = function(){
        Auth.logoutUser();
        return $state.go('index');
    };

}]);