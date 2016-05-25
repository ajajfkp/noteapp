app.controller('authCtrl', function ($scope, $location, $rootScope, Data, toaster) {
    //initially set those objects to null to avoid undefined error
    $scope.login = {};
    $scope.doLogin = function (customer) {
        Data.post('login', {
            userLogin: customer
        }).then(function (results) {
            if (results.status == "success") {
				$rootScope.authenticated = true;
				localStorage.authenticated = $rootScope.authenticated;
				localStorage.uid = results.id;
                $location.path('dashboard');
            }else{
				toaster.pop('success', "", results.msg);
				$rootScope.authenticated = false;
				localStorage.authenticated = $rootScope.authenticated;
			}
        });
    };
    
});