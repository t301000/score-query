angular.module('myapp')
.factory('Backend',['$http', '$q',
function($http, $q)
{
    var obj = {};

    // 取得 user list
    obj.getUserList = function(){
        var deferred = $q.defer();

        $http.get('/users')
            .success(function(response){
                deferred.resolve(response);
            })
            .error(function(error){
                console.log(error);
                deferred.reject(error);
            });

        return deferred.promise;
    };

    // 以 id 取得 user 資料
    obj.getUserData = function(id){
        var deferred = $q.defer();

        $http.get('/users/:id',{id: id})
            .success(function(user){
                deferred.resolve(user);
            })
            .error(function (error) {
                console.log(error);
                deferred.reject(error);
            });

        return deferred.promise;

    };

    return obj;
}]);