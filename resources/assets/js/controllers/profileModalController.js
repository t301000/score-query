angular.module('myapp')
.controller('profileModalController',
['$modalInstance', 'userData', function ($modalInstance, userData) {
    var vm = this;

    //userData 為傳入之 restangular 物件
    vm.userData = userData;
    console.log(vm.userData);

    // 決定 view 要不要顯示變更密碼的欄位
    //vm.isLocalUser = $root.me.provider === 'local';

    // 按下確定紐關閉 modal，將 restangular 物件回傳給 navController
    vm.ok = function () {
        $modalInstance.close(vm.userData);
    };

    // 按下取消紐關閉 modal
    vm.cancel = function () {
        $modalInstance.dismiss();
    };

}]);