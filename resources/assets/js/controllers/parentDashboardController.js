angular.module('myapp')
.controller('ParentDashboardController', ['Restangular', '$modal',
function (Restangular, $modal) {
    var vm = this;

    vm.stuList = [];
    vm.scores = [];
    vm.msg = '載入中....';
    //vm.stuList = Restangular.allUrl('manage-link').getList().$object;

    var loadList = function(){
        Restangular.allUrl('manage-link').getList().then(
            function(response){
                vm.stuList = response;
                if(!vm.stuList.length){
                    vm.msg = '請先新增學生';
                }
            }
        );
    };

    vm.del = function(item){
        console.log(item.id);
        Restangular.one('manage-link', item.id).remove().then(
            function(response){
                vm.stuList.splice(vm.stuList.indexOf(item), 1);
            }
        );
    };

    vm.showModal = function(){
        var modalInstance = $modal.open({
            templateUrl: 'partials/modals/findStu.html',
            controller: 'FindStuModalInstanceCtrl',
            controllerAs: 'ModalCtrl',
            size: 'sm'
        });

        modalInstance.result
            .then(function (stu) {
                var postData = {
                    class_id: stu.class_id,
                    stu_id: stu.id
                };
                Restangular.allUrl('manage-link').post(postData).then(
                  function(response){
                      vm.stuList.push(stu);
                  }
                );
            }, function () {
                console.info('Find Stu Modal dismissed at: ' + new Date());
            });
    };

    vm.showScore = function (stu) {
        vm.currStu = stu;
        vm.scores = [];
        vm.msgScore = '資料載入中....';
        Restangular.one('query-scores', stu.id).get().then(
            function(response){
                vm.scores = response.scores;
                if(!vm.scores.length){
                    vm.msgScore = '沒有資料';
                }
            }
        );
    };

    loadList();

}])

.controller('FindStuModalInstanceCtrl', ['$modalInstance', 'Restangular', function ($modalInstance, Restangular) {
    var vm = this;
    var step = 'step1';

    vm.ok = function () {
        $modalInstance.close(vm.foundStu);
    };

    vm.search = function(){
        Restangular.oneUrl('findClassByCode', 'find-by-code/' + vm.stu.classCode + '/' + vm.stu.stuCode).get()//.customGET('findClassByCode', {code: vm.stu.classCode})
            .then(
                function(response){
                    if(response.stu){
                        vm.foundClass = response.class;
                        vm.foundStu = response.stu;
                    }else{
                        vm.hasError = true;
                    }
                    step = 'step2';
                }
        );
    };

    // 依據步驟決定哪些 ui element 要顯示
    vm.show = function(check){
        return step == check;
    };

    vm.cancel = function () {
        $modalInstance.dismiss('cancel');
    };

    vm.prevStep = function(){
        vm.hasError = false;
        step = 'step1';
    };
}]);