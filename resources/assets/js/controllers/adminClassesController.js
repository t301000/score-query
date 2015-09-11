angular.module('myapp')
.controller('AdminClassesController',['Restangular',
function(Restangular) {
    var vm = this;

    var base = Restangular.all('manage-classrooms');

    base.getList().then(
        function(response){
            vm.lists = response;
        }
    );

}]);