angular.module('myapp')
.controller('ExamsController', ['$rootScope', '$state', '$stateParams', 'Restangular', '$modal', 'classroom',
function ($rootScope, $state, $stateParams, Restangular, $modal, classroom) {
    var vm = this;
    //var classID = $stateParams.id;
    var base = Restangular.one('classrooms', classroom.id).all('exams');

    vm.exams = base.getList().$object;

    vm.delete = function(item){
        console.log("Delete: ");
        console.log(item);
        item.remove().then(
            function(response){
                vm.exams.splice(vm.exams.indexOf(item),1);
            });
    }

    vm.getYesNoText = function(bool){
        return bool ? '是' : '否';
    }

    // 新增或編輯 form
    vm.showModal = function(exam){
        var modalInstance = $modal.open({
            templateUrl: 'partials/modals/examForm.html',
            controller: 'ExamModalInstanceCtrl',
            controllerAs: 'ModalCtrl',
            size: 'sm',
            resolve:{
                exam: function(){
                    return exam;
                }
            }
        });

        modalInstance.result
            .then(function (data) {
                console.log(data);
                if(data.id){
                    // 更新
                    data.save();
                } else {
                    // 新增
                    base.post(data).then(
                        function (response) {
                            vm.exams = base.getList().$object;
                        }
                    );
                }
            }, function () {
                console.info('Exam Form Modal dismissed at: ' + new Date());
            });
    };


}])

.controller('ExamModalInstanceCtrl', ['$modalInstance', 'exam', function ($modalInstance, exam) {
    var vm = this;
    var newExam = {
        grade: 7,
        half: '上',
        name: '',
        social_merge: false
    };

    //vm.exam = exam || angular.copy(newExam);
    vm.exam = exam || newExam;
    vm.title = exam ? '編輯' : '新增';

    vm.ok = function () {
        $modalInstance.close(vm.exam);
    };

    vm.cancel = function () {
        $modalInstance.dismiss('cancel');
    };
}]);