angular.module('myapp')
.controller('AdminSettingsController',['$rootScope', 'Restangular', 'lodash',
function($rootScope, Restangular, lodash){
    var vm = this;
    var base = Restangular.all('schools');

    vm.schoolList = base.getList().$object;

    vm.addSchoolData = function(data){
        base.post(data).then(
            function(response){
                if(response.messages[0].type==='success'){
                    // 重新取得清單
                    //vm.schoolList = base.getList().$object;

                    // 將回傳之新學校資料 restangularize 後 push 進清單陣列中，如此可不必重新取得完整清單
                    vm.schoolList.push( Restangular.restangularizeElement(null, response.newSchool, base.route) );
                    //console.log(vm.schoolList);
                    vm.new_school = {};
                }
            }
        );
    };

    vm.deleteSchool = function(item){
        item.remove().then(function(response){
            if(response.messages[0].type==='success'){
                vm.schoolList.splice(vm.schoolList.indexOf(item),　1);
            }
        });
    }

}]);