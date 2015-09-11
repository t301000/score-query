angular.module('myapp')
.controller('StudentsController', ['$rootScope', '$state', '$stateParams', 'Restangular', '$modal', 'classroom',
function ($rootScope, $state, $stateParams, Restangular, $modal, classroom) {
    var vm = this;
    var classID = $stateParams.id;
    var base = Restangular.one('classrooms', classID);

    //新學生資料，可能多筆，所以用陣列儲存
    vm.newStudents = [];

    getStudents();

    vm.newStudent = function (singleStuData) {
        if(angular.isObject(singleStuData)) {
            vm.newStudents.push(singleStuData);
        }
        base.post('students', vm.newStudents).then(
            function(response){
                vm.newStudents = [];
                getStudents();
            },
            function(error){
                console.log(error);
            }
        );

    };

    vm.edit = function(stu){
        var modalInstance = $modal.open({
            templateUrl: 'partials/modals/editStudentForm.html',
            controller: 'EditStuFormModalInstanceCtrl',
            controllerAs: 'ModalCtrl',
            size: 'sm',
            resolve: {
                stu: function() {
                    return stu;
                }
            }
        });

        modalInstance.result.then(function () {
            //console.log(stu);
            stu.save();
        }, function () {
            console.info('Edit Student Modal dismissed at: ' + new Date());
        });
    };

    vm.delete = function(item){
        item.remove().then(
            function(response){
                vm.students.splice(vm.students.indexOf(item), 1);
            },
            function (error) {
                console.log(error);
            }
        );

    };

    vm.exportToExcel = function(){
        //var filename = classroom.class_name + "(班級代碼：" + classroom.class_code + ")學生名單.xlsx";
        var filename = classroom.class_name.concat("(班級代碼：", classroom.class_code, ")學生名單.xlsx");
        alasql('SELECT num,name,link_code INTO XLSX("' + filename + '", {headers: true}) FROM ?', [vm.students]);
    };

    vm.showNewStuFormModal = function(){
        var modalInstance = $modal.open({
            templateUrl: 'partials/modals/newStudentForm.html',
            controller: 'NewStuFormModalInstanceCtrl',
            controllerAs: 'ModalCtrl',
            size: 'sm'
        });

        modalInstance.result.then(function (data) {
            //vm.newStudents = data;
            vm.newStudent(data);
        }, function () {
            console.info('Import Modal dismissed at: ' + new Date());
        });
    };

    vm.showImportModal = function(){
        var modalInstance = $modal.open({
            templateUrl: 'partials/modals/importStudents.html',
            controller: 'ImportStuModalInstanceCtrl',
            controllerAs: 'ImportStuModalCtrl',
            size: 'sm'
        });

        modalInstance.result.then(function (data) {
            vm.newStudents = data;
            vm.newStudent();
        }, function () {
            console.info('Import Modal dismissed at: ' + new Date());
        });
    };

    function getStudents(){
        base.getList('students').then(
            function (response){
                vm.students = response;
            },
            function (error) {
                console.log(error);
            }
        );
    }

}])

.controller('ImportStuModalInstanceCtrl', ['$modalInstance','XLSXReaderService', function ($modalInstance, XLSXReaderService) {
    var vm = this;

    vm.showPreview = false; //以表格預覽
    vm.showJSONPreview = true; //以json預覽
    vm.items = [];//存放結果物件之陣列，一列為一個物件，key為欄名
    vm.sheets = [];

    //選擇檔案或選擇另一個檔案
    //取得工作表 array
    vm.fileChanged = function(files) {
        vm.isProcessing = true; //處理中之 flag
        vm.msg = "讀取中....";
        vm.sheets = []; //工作表 array
        vm.excelFile = files[0];//選擇之excel檔(.xlsx格式)

        XLSXReaderService.readFile(vm.excelFile, vm.showPreview, vm.showJSONPreview)
            .then(function (xlsxData) {
                vm.sheets = xlsxData.sheets;
                vm.isProcessing = false;
                vm.msg = '';
            });

    };

    //為了讓 view 中呼叫，bind to $scope
    //$scope.fileChanged = vm.fileChanged;

    // 選擇工作表時更新結果物件陣列
    vm.updateItems = function() {
        vm.items = vm.sheets[vm.selectedSheetName];
    }

    vm.ok = function () {
        $modalInstance.close(vm.items);
    };

    vm.cancel = function () {
        $modalInstance.dismiss('cancel');
    };
}])

.controller('NewStuFormModalInstanceCtrl', ['$modalInstance', function ($modalInstance) {
    var vm = this;

    vm.ok = function () {
        $modalInstance.close(vm.newStu);
    };

    vm.cancel = function () {
        $modalInstance.dismiss('cancel');
    };
}])

.controller('EditStuFormModalInstanceCtrl', ['$modalInstance', 'stu', '$http', function ($modalInstance, stu, $http) {
    var vm = this;
    vm.stu = stu;

    vm.regenerateLinkCode = function(stuID){
        $http.get('reget-link-code/' + stuID).then(
            function(response){
                console.log(response);
                vm.stu.link_code = response.data;
            }
        );

    };

    vm.ok = function () {
        $modalInstance.close();
    };

    vm.cancel = function () {
        $modalInstance.dismiss('cancel');
    };
}]);