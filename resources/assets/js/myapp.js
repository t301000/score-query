angular
.module('myapp', [
    'ui.router',
    'ngStorage',
    'ngTasty',
    'angular-jwt',
    'oc.lazyLoad',
    'ui.bootstrap',
    'angular-growl',
    'restangular',
    'angular-loading-bar',
    'ngLodash'
    ])

.constant('API', '/')
.constant('MyAppConfig', {
    TimeToRefreshToken: 5, // 要更新token的時間(分鐘)
})

.config(['$httpProvider', 'jwtInterceptorProvider', '$stateProvider', '$urlRouterProvider', '$ocLazyLoadProvider', 'growlProvider', 'cfpLoadingBarProvider', 'RestangularProvider',
    function($httpProvider, jwtInterceptorProvider, $stateProvider, $urlRouterProvider, $ocLazyLoadProvider, growlProvider, cfpLoadingBarProvider, RestangularProvider)
{
    RestangularProvider.setBaseUrl = 'score';

    jwtInterceptorProvider.tokenGetter =['jwtHelper', '$http', 'Auth', 'config', function(jwtHelper, $http, Auth, config) {

        if (config.url.substr(config.url.length - 5) == '.html') {
            return null;
        }

        var idToken = Auth.getToken();

        //var refreshToken = localStorage.getItem('refresh_token');
        if ( idToken && Auth.willTokenExpired(idToken) ) {
            console.log('token need refresh...');
            // This is a promise of a JWT id_token
            return $http({
                url: 'auth/refresh_jwt',
                // This makes it so that this request doesn't send the JWT
                skipAuthorization: true,
                method: 'POST',
                data: {
                    //grant_type: 'refresh_token',
                    old_token: idToken
                }
            }).then(function(response) {
                var new_token = response.data.token;
                console.log('return new token...');
                //Auth.saveToken( new_token );
                return new_token;
            });
        } else {
            return idToken;
        }
    }];
    $httpProvider.interceptors.push('jwtInterceptor');
    $httpProvider.interceptors.push('authInterceptor');

    // disable spinner, only loading bar
    cfpLoadingBarProvider.includeSpinner = false;

    growlProvider.globalTimeToLive({success: 2000, error: 3000, warning: 3000, info: 3000});
    growlProvider.globalDisableCountDown(true);
    growlProvider.globalPosition('bottom-right');
    growlProvider.messagesKey("messages");
    growlProvider.messageTextKey("content");
    growlProvider.messageTitleKey("title");
    growlProvider.messageSeverityKey("type");


    $httpProvider.interceptors.push(growlProvider.serverMessagesInterceptor);

    //$ocLazyLoadProvider.config ({
    //    debug: true
    //});

    $urlRouterProvider.otherwise("/");
    $stateProvider
        .state('index', {
            url: "/",
            templateUrl: "partials/index.html",
            data: {
                requireLogin: false
            },
            //controllerAs: 'index',
            //resolve: {
            //    loadCtrl: ['$ocLazyLoad', function($ocLazyLoad){
            //        return $ocLazyLoad.load('build/js/controllers/dashboardController.min.js');
            //    }]
            //}
        })

        .state('auth', {
            abstract: true,
            template: '<ui-view />',
            data: {
                requireLogin: true
            }
        })
        // 登入後轉至此，依據 role 轉到對應的 state
        .state('auth.dashboard', {
            url: "/dashboard",
            template: "",
            controller: "DashboardController",
            //controllerAs: 'dashboard',
            resolve: {
                loadCtrl: ['$ocLazyLoad', function($ocLazyLoad){
                    return $ocLazyLoad.load('build/js/controllers/dashboardController.min.js');
                }]
            }
        })

        .state('auth.admin', {
            url: "/admin",
            templateUrl: "partials/admin/dashboard.html",
            data: {
                requireRole: ['admin']
            }
        })
        .state('auth.admin.users', {
            url: "/users",
            templateUrl: "partials/admin/users.html",
            controller: "AdminUsersController",
            controllerAs: 'adminUsers',
            resolve: {
                loadCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load('build/js/controllers/adminUsersController.min.js');
                }]
            }
        })
        .state('auth.admin.classes', {
            url: "/classes",
            templateUrl: "partials/admin/classes.html",
            controller: "AdminClassesController",
            controllerAs: 'AdminClasses',
            resolve: {
                loadCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load('build/js/controllers/adminClassesController.min.js');
                }]
            }
        })
        .state('auth.admin.settings', {
            url: "/settings",
            templateUrl: "partials/admin/settings.html",
            controller: "AdminSettingsController",
            controllerAs: 'vmCtrl',
            data: {
                requireRole: ['admin']
            },
            resolve: {
                loadCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load('build/js/controllers/adminSettingsController.min.js');
                }]
            }
        })

        .state('auth.teacher', {
            url: "/teacher",
            templateUrl: "partials/teacher/dashboard.html",
            controller: "TeacherDashboardController",
            controllerAs: 'teacherDashboard',
            data: {
                requireRole: ['teacher']
            },
            resolve: {
                loadCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load('build/js/controllers/teacherDashboardController.min.js');
                }]
            }
        })
        .state('auth.teacher.new-class', {
            url: "^/classrooms",
            templateUrl: "partials/teacher/newClassForm.html",
            controller: "NewClassController",
            controllerAs: 'NewClass',
            resolve: {
                loadCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load('build/js/controllers/newClassController.min.js');
                }]
            }
        })
        .state('auth.teacher.edit-class', {
            url: "^/classrooms/:id",
            templateUrl: "partials/teacher/editClassForm.html",
            controller: "EditClassController",
            controllerAs: 'EditClass',
            resolve: {
                loadCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load('build/js/controllers/editClassController.min.js');
                }]
            }
        })
        .state('auth.teacher.students', {
            url: "^/classrooms/:id/students",
            templateUrl: "partials/teacher/students.html",
            controller: "StudentsController",
            controllerAs: 'Student',
            resolve: {
                classroom: ['Restangular', '$stateParams', function(Restangular, $stateParams){
                    return Restangular.one('classrooms', $stateParams.id).get()
                            .then(
                                function(data){
                                    return data;
                                });
                }],
                loadCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load([
                        'build/js/controllers/studentsController.min.js',
                        'build/js/alasql.min.js'
                    ]);
                }]
            }
        })
        .state('auth.teacher.exams', {
            url: "^/classrooms/:id/exams",
            templateUrl: "partials/teacher/exams.html",
            controller: "ExamsController",
            controllerAs: 'Exam',
            resolve: {
                classroom: ['Restangular', '$stateParams', function(Restangular, $stateParams){
                    return Restangular.one('classrooms', $stateParams.id).get()
                        .then(
                            function(data){
                                return data;
                        });
                }],
                loadCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load('build/js/controllers/examsController.min.js');
                }]
            }
        })
        .state('auth.teacher.scores', {
            url: "^/classrooms/:id/exams/:eid/scores",
            templateUrl: "partials/teacher/exam-scores.html",
            controller: "ScoresController",
            controllerAs: 'Score',
            resolve: {
                exam: ['Restangular', '$stateParams', function(Restangular, $stateParams){
                    return Restangular.one('classrooms', $stateParams.id)
                        .one('exams', $stateParams.eid).get()
                        //.all('scores')
                        //.getList()
                        .then(
                            function(data){
                                return data;
                        });
                }],
                loadCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load('build/js/controllers/scoresController.min.js');
                }]
            }
        })

        .state('auth.parents', {
            url: "/parents",
            templateUrl: "partials/parents/dashboard.html",
            controller: "ParentDashboardController",
            controllerAs: 'ParentDashboard',
            data: {
                requireRole: ['parents']
            },
            resolve: {
                loadCtrl: ['$ocLazyLoad', function ($ocLazyLoad) {
                    return $ocLazyLoad.load('build/js/controllers/parentDashboardController.min.js');
                }],
                //isAuthenticated: ['Auth', function(Auth){
                //    return Auth.isAuthenticated();
                //}]
            }
        });

}])

.run(['$rootScope', 'Auth', 'loginModal', '$state', function ($rootScope, Auth, loginModal, $state) {

    $rootScope.$on('$stateChangeStart', function (event, toState, toParams) {
        var requireLogin = toState.data.requireLogin;
        var requireRole = toState.data.requireRole;

        // 需要登入 且 未登入或已逾期
        // 則登出 user 並彈出登入 modal
        if ( requireLogin && (!Auth.isAuthenticated() || Auth.isTokenExpired()) ) {
            event.preventDefault();

            loginModal()
                .then(function () {
                    //return $state.go(toState.name, toParams);
                })
                .catch(function () {
                    return $state.go('index');
                });

            return;
        }else if( Auth.isAuthenticated()　&& Auth.isTokenExpired() ){
            // 已登入，但逾期
            console.log('token已逾期....');
            return $state.go(toState, toParams);
        }

        // 如果有要求權限，則檢查是否有權限
        if( requireRole && requireRole.length && !Auth.hasPermission(requireRole) ){
            console.log('權限不足....');
            event.preventDefault();
            $rootScope.$broadcast('permission_deny');
        }
    });

}]);