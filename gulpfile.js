var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

// elixir(function(mix) {
//     mix.sass('app.scss');
// });

var base_dir = 'bower_components';

elixir(function(mix) {
    mix
    .styles([
            'bootswatch/paper/bootstrap.css',
            'ionicons/css/ionicons.css',
            'angular-growl-v2/build/angular-growl.css',
            'angular-loading-bar/build/loading-bar.css',
            '../resources/assets/css/myapp.css'
        ], 'public/build/css/bundle.min.css', base_dir)
	.copy(base_dir + '/bootstrap/dist/fonts', 'public/build/fonts')
	.copy(base_dir + '/ionicons/fonts', 'public/build/fonts')
    .scripts([
            'angular/angular.js',
            'angular-animate/angular-animate.js',
            'angular-ui-router/release/angular-ui-router.js',
            'ocLazyLoad/dist/ocLazyLoad.js',
            'angular-bootstrap/ui-bootstrap-tpls.js',
            'ng-tasty/ng-tasty-tpls.js',
            'angular-growl-v2/build/angular-growl.js',
            'ngstorage/ngStorage.js',
            'angular-jwt/dist/angular-jwt.js',
            'lodash/lodash.js',
            'ng-lodash/build/ng-lodash.js',
            'restangular/dist/restangular.js',
            'angular-loading-bar/build/loading-bar.js',
            'js-xlsx/dist/jszip.js',
            'js-xlsx/dist/xlsx.js',
            'xlsx-reader.js/xlsx-reader.js'
        ], 'public/build/js/bundle.min.js', base_dir)
    .scripts([
            'myapp.js',
            'services/**',
            'controllers/applicationController.js',
            'controllers/navController.js',
            'controllers/profileModalController.js'
        ], 'public/build/js/myapp-all.min.js')
    .scripts(['controllers/loginController.js'], 'public/build/js/controllers/loginController.min.js')
    .scripts(['controllers/dashboardController.js'], 'public/build/js/controllers/dashboardController.min.js')
    .scripts(['controllers/adminUsersController.js'], 'public/build/js/controllers/adminUsersController.min.js')
    .scripts(['controllers/adminSettingsController.js'], 'public/build/js/controllers/adminSettingsController.min.js')
    .scripts(['controllers/adminClassesController.js'], 'public/build/js/controllers/adminClassesController.min.js')
    .scripts(['controllers/teacherDashboardController.js'], 'public/build/js/controllers/teacherDashboardController.min.js')
    .scripts(['controllers/newClassController.js'], 'public/build/js/controllers/newClassController.min.js')
    .scripts(['controllers/editClassController.js'], 'public/build/js/controllers/editClassController.min.js')
    .scripts(['controllers/studentsController.js'], 'public/build/js/controllers/studentsController.min.js')
    .scripts(['controllers/examsController.js'], 'public/build/js/controllers/examsController.min.js')
    .scripts(['controllers/scoresController.js'], 'public/build/js/controllers/scoresController.min.js')
    .scripts(['controllers/parentDashboardController.js'], 'public/build/js/controllers/parentDashboardController.min.js');

});

