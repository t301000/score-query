angular.module('myapp')
.service('loginModal', ['$modal', '$rootScope', '$ocLazyLoad', function ($modal, $rootScope, $ocLazyLoad) {

    return function() {
        var instance = $modal.open({
            templateUrl: 'partials/modals/login.html',
            size: 'sm',
            controller: 'LoginController',
            controllerAs: 'login',
            resolve:{
                loadCtrl: ['$ocLazyLoad', function($ocLazyLoad){
                    return $ocLazyLoad.load('build/js/controllers/loginController.min.js');
                }],
                isAuthenticated: ['Auth', function(Auth){
                    return Auth.isAuthenticated();
                }]
            }
        });

        $rootScope.$on('UserLogined', function(){
            instance.close();
        });

        return instance.result;
    };

}]);