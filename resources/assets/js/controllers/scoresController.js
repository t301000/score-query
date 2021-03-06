angular.module('myapp')
.controller('ScoresController', ['$rootScope', '$state', '$stateParams', 'Restangular', '$modal', 'exam', '$http',
function ($rootScope, $state, $stateParams, Restangular, $modal, exam, $http) {
    var vm = this;
    var base = exam.all('scores');

    vm.exam = exam;
    vm.msg = '載入中....';

    // default th
    var thStyle = {"text-align": 'right'};
    var header = [
        { "key": "num", "name": "座號", "style": {"text-align": 'center'} },
        { "key": "name", "name": "姓名", "style": {"text-align": 'center'} },
        { "key": "chinese", "name": "國文", "style": thStyle },
        { "key": "english", "name": "英文", "style": thStyle },
        { "key": "math", "name": "數學", "style": thStyle },
        { "key": "science", "name": "自然", "style": thStyle },
        { "key": "social", "name": "社會", "style": thStyle },
        { "key": "avg", "name": "平均", "style": thStyle },
        { "key": "total", "name": "總分", "style": thStyle },
        { "key": "rank", "name": "名次", "style": thStyle },
    ];

    // 如果社會不是合科（也就是分科顯示）
    // 加入分科之th
    if(!vm.exam.social_merge){
        header.splice(7, 0,
            { "key": "history", "name": "歷史", "style": thStyle },
            { "key": "geo", "name": "地理", "style": thStyle },
            { "key": "civic", "name": "公民", "style": thStyle }
        );
    }

    vm.tasty = {
        // 不用來排序之欄位th key
        // 空陣列表示都不排序
        notSortBy: [],
        // 表格資料
        resource: {
            "header": header,
            "rows": [],
            //"sortBy": "",
            "sortOrder": "asc" // asc遞增，dsc遞減
        }
    };
    // 表格資料
    //vm.resource = {
    //    "header": header,
    //    "rows": [],
    //    "sortBy": "num",
    //    "sortOrder": "asc" // asc遞增，dsc遞減
    //};

    base.getList().then(
        function(data){
            vm.msg = (data.length > 0) ? '':'請匯入成績';
            vm.tasty.resource.rows = data;
        }
    );

    vm.showImportModal = function(){
        var modalInstance = $modal.open({
            templateUrl: 'partials/modals/importScores.html',
            controller: 'ImportScoreModalInstanceCtrl',
            controllerAs: 'ImportScoreModalCtrl',
            size: 'lg',
            resolve:{
                tasty: function(){
                    return angular.copy(vm.tasty);
                },
                title: function(){
                    return "(" + vm.exam.grade + vm.exam.half + ") " + vm.exam.name;
                }
            }
        });

        modalInstance.result
            .then(function (data) {
                vm.msg = '資料處理中....';
                base.post(data).then(function(response){
                    vm.msg = '';
                    vm.tasty.resource.rows = base.getList().$object;
                });
            }, function () {
                console.info('Import Modal dismissed at: ' + new Date());
            });
    };

    vm.showAllScoresForStu =function(id){
        var modalInstance = $modal.open({
            templateUrl: 'partials/modals/showAllScoresForStu.html',
            controller: 'ShowAllScoresForStuModalInstanceCtrl',
            controllerAs: 'ModalCtrl',
            size: 'lg',
            resolve:{
                data: function(){
                    return $http.get('query-scores/' + id).then(
                        function(response){
                            return response.data;
                        }
                    );
                }
            }
        });
    };

}])

.controller('ImportScoreModalInstanceCtrl', ['$modalInstance','XLSXReaderService', 'tasty', 'title', function ($modalInstance, XLSXReaderService, tasty, title) {
    var vm = this;

    vm.showPreview = false; //以表格預覽
    vm.showJSONPreview = true; //以json預覽
    vm.items = [];//存放結果物件之陣列，一列為一個物件，key為欄名
    vm.sheets = [];
    vm.tasty = tasty;
    vm.title = title;

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

    // 選擇工作表時更新結果物件陣列
    vm.updateItems = function() {
        vm.items = vm.sheets[vm.selectedSheetName];
        vm.tasty.resource.rows = vm.items;
    }

    vm.ok = function () {
        $modalInstance.close(vm.items);
    };

    vm.cancel = function () {
        $modalInstance.dismiss('cancel');
    };
}])

.controller('ShowAllScoresForStuModalInstanceCtrl', ['$modalInstance', 'data', function ($modalInstance, data) {
    var vm = this;
    vm.data = data;
    vm.cancel = function () {
        $modalInstance.dismiss();
    };
}]);