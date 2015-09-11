angular.module('myapp')
.controller('AdminUsersController',['$rootScope', 'Restangular', 'lodash',
function($rootScope, Restangular, lodash){
    var vm = this;

    //　參考資料：
    //　http://zizzamia.com/ng-tasty/directive/table-server-side/complete
    // pagination 初始化
    vm.init = {
        'count': 10, // 一頁幾筆
        'page': 1, // 目前頁數
        'sortBy': 'name', // 排序欄位
        'sortOrder': 'asc' // 遞增 asc 或遞減 dsc(不是desc)
    };

    // 搜尋條件物件，預設列出 admin teacher
    vm.filterBy = {
        roles: {
            admin: true,
            teacher: true,
            parents: false
        }
    };
    // filter 改變時執行
    //vm.search = function(params){
    //    console.log(vm.filterBy);
    //    console.log(params);
    //};

    // 取得分頁資料
    // 參考資料 http://zizzamia.com/ng-tasty/directive/table-server-side/complete
    vm.getResource = function (params, paramsObj) {
        paramsObj.filterBy = vm.filterBy;
        //console.log(params);
        return Restangular.one('users/page', paramsObj.page).get(paramsObj).then(
            function(pageData){
                //console.log(pageData);

                return {
                    'rows': pageData.data, // 分頁表格內容
                    'header': [ // 要當作分頁表格 thead 的資料欄位 key ，name 為顯示之文字
                        //{ "key": "id", "name": "ID" },
                        { "key": "name", "name": "帳號" },
                        { "key": "real_name", "name": "真實姓名" },
                        { "key": "email", "name": "Email" },
                        { "key": "roles", "name": "角色" }
                    ],
                    'pagination':  {
                        "count": pageData.per_page, // 一頁筆數
                        "page": pageData.current_page, // 當前頁數
                        "pages": pageData.last_page, // 總頁數
                        "size": pageData.total // 資料總筆數
                    },
                    'sortBy': 'real_name', // 排序欄位
                    'sortOrder': 'asc' // 遞增或遞減
                };

            },
            function(error){
                console.log(error);
            });
    };

    // pagination 樣板檔路徑
    // 參考資料 https://github.com/Zizzamia/ng-tasty/issues/86
    vm.paginationTemplate = 'partials/pagination.html';

    // 檢查 user 是否具有某種角色
    // checkRole ==> 要檢查的 role name，admin、teacher、parents
    // userRoles ==> 物件陣列，例如：
    //          [
    //              {id: "1", name: "admin", pivot: {user_id: "1", role_id: "1"}},
    //              {id: "2", name: "teacher", pivot: {user_id: "1", role_id: "2"}}
    //          ]
    vm.checkPermission = function( checkRole, userRoles ){

        // 用 lodash 取出 user 具備的 role name array
        //　roles ==> ['admin', 'teacher']
        var roles = lodash.map(userRoles, 'name');

        return roles.indexOf(checkRole) !== -1;
    };

    // 變更角色設定
    vm.togglePermission = function(userID, roleName){
        Restangular.one('users', userID).put({mode: 'toggle_role', roleName: roleName}, {ignoreLoadingBar: true});
    }


}]);