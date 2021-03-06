angular.module('myapp')
.controller('TeacherDashboardController', ['$rootScope', 'Restangular', 'lodash', '$state',
    function ($rootScope, Restangular, lodash, $state) {
        var vm = this;

        vm.msg = '載入中....';

        //vm.classList = Restangular.all('classrooms').getList().$object;
        vm.reloadList = function(){
            Restangular.all('classrooms').getList().then(
                function(classList){
                    vm.classList = classList;
                    if(!classList.length){
                        vm.msg = '請先新增班級';
                    }
                }
            );
        }

        vm.reloadList();

        vm.setNowIndex = function(index){
            vm.nowIndex = index;
        };

        // 監聽：new_class_created 新增班級完成
        $rootScope.$on('new_class_created', function(event, newClassData){
            vm.classList.push(newClassData);
        });

        // 監聽：class_deleted 刪除班級完成
        $rootScope.$on('class_deleted', function(event, deletedClass){
            lodash.remove(vm.classList, function(item){
                return item.id == deletedClass.id;
            });
            if(!vm.classList.length){
                vm.msg = '請先新增班級';
            }
            $state.go('auth.teacher');
        });

        // 監聽：class_updated 班級資料已更新
        $rootScope.$on('class_updated', function(event, newClassData){
            //vm.classList.push(newClassData);
            //console.log(newClassData);
            //console.log(lodash.findWhere(vm.classList, {id: newClassData.id}));

            // 從 vm.classList 中取出更新的物件，以 server 回傳新的資料更新屬性值
            angular.extend( lodash.findWhere(vm.classList, {id: newClassData.id}), newClassData );
            //console.log(vm.classList);
        });

    }]);