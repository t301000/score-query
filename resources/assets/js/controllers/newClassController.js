angular.module('myapp')
.controller('NewClassController', ['$rootScope', '$state', 'Restangular',
function($rootScope, $state, Restangular)
{
    var vm = this;

    //產生入學學年度預設值
    var d = new Date();
    vm.classObj = {
        school_year_in: (d.getMonth() < 7) ? d.getFullYear()-1912 : d.getFullYear()-1911
    };

    vm.createClass = function(){
        Restangular.all('classrooms').post(vm.classObj).then(function(response){
            if(response.messages[0].type === 'success') {
                $rootScope.$broadcast('new_class_created', response.new_class);
            }
            return $state.go('auth.teacher');
        });

    };
}]);