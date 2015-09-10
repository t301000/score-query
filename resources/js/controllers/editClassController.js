angular.module('myapp')
.controller('EditClassController', ['$rootScope', '$state', '$stateParams', 'Restangular',
function($rootScope, $state, $stateParams, Restangular)
{
    var vm = this;

    //vm.classObj = $scope.classList[$stateParams.id];
    Restangular.one('classrooms', $stateParams.id).get().then(
        function(classData){
            vm.classObj = classData;
            vm.classObj.school_year_in = parseInt(classData.school_year_in);
        }
    );

    //更新班級資料
    vm.updateClass = function(){
        vm.classObj.put().then(function(response){
            $rootScope.$broadcast('class_updated', response.new_data);

            return $state.go('auth.teacher');
        });
    };

    //刪除班級
    vm.confirmDelete = function(){
        vm.classObj.remove().then(
            function(response){
                $rootScope.$broadcast('class_deleted', {id: $stateParams.id});
            },
            function(error){
                console.log(error);
            }
        );
    };

}]);