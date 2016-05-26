var app = angular.module('noteMpdule',['ngRoute','infinite-scroll','toaster']);
	app.config(['$routeProvider', function ($routeProvider) {
		$routeProvider.when('/login', {
            title: 'Login',
            templateUrl: 'partials/login.html',
            controller: 'authCtrl'
        })
		.when('/dashboard', {
			title: 'Dashboard',
			templateUrl: 'partials/dashboard.html',
			controller: 'dashboardCtrl'
		})
		.when('/edit-note/:noteId', {
			title: 'Edit Note',
			templateUrl: 'partials/addeditnote.html',
			controller: 'addeditCtrl',
			resolve: {
			  usernote: function(Data, $route){
				var noteId = $route.current.params.noteId;
				return Data.get('getnote',{
					params:{
						noteid:noteId
					}
				}).then(function (results) {
					return results;
				});
			  }
			}
		})
		.otherwise({
			redirectTo: '/login'
		});
	}]).run(function ($rootScope, $location, Data) {
		$rootScope.$on("$routeChangeStart", function (event, next, current) {
			if(localStorage.authenticated && localStorage.authenticated == "true"){
				$rootScope.authenticated = localStorage.authenticated;
                $rootScope.uid = localStorage.uid;
			}else{
				$rootScope.authenticated = false;
				localStorage.authenticated = false;
				localStorage.uid='';
				var nextUrl = next.$$route.originalPath;
				if (nextUrl == '/login') {

				} else {
					$location.path("/login");
				}
			}
      
        });
    });
  
	app.controller('dashboardCtrl', function ($scope, $rootScope, $route, Data, $filter) {
		//console.log(infiniteScroll);
		Data.get('getnotes',{
			params:{
				userId:$rootScope.uid,
				limit: 4, 
				offset: 0
			}
		}).then(function (results) {
			$scope.usernotes = results;
			var offset = 4;
			var limit = 5;
			$scope.loadmoreChk = false;
			$scope.busy = false;
			
			$scope.loadMore = function() {
				if ($scope.busy) {
						return;
				}
				$scope.loadmoreChk = true;
				$scope.busy = true;
				Data.get('getnotes',{
					params:{
						userId:$rootScope.uid,
						limit: limit, 
						offset: offset
					}
				}).then(function (results) {
					if(results){
						$scope.usernotes = $scope.usernotes.concat(results);
						$scope.busy = false;
						offset = offset+limit;
					}
					$scope.loadmoreChk = false;
				});
			};
			$scope.deleteNote = function(usernote) {
				var confirms = confirm("Are you sure to delete note number: "+usernote.id);
				if(confirms==true){
					$route.reload();
					Data.delete('deleteNote',{
						params:{
							id:usernote.id
						}
					}).then(function (results) {
						$scope.resp = results;
					});
				}			
			};
			
		});
		
		
		
		
		
		
		 $scope.deleteSelected = function() {
			 var selectIds=""
			for(i in $scope.selected){
				selectIds += $scope.selected[i]['id']+",";
			}
			selectIds = selectIds.slice(0,-1);
			var confirms = confirm("Are you sure to delete note numbers: "+selectIds);
			if(confirms==true){
				$route.reload();
				Data.delete('deleteNote',{
					params:{
						id:selectIds
					}
				}).then(function (results) {
					$scope.resp = results;
				});
			}
		}
		
		$scope.$watch( "usernotes" , function(n,o){
            var checked= $filter("filter")( n , {checked:true} );
            if(checked){
                $scope.selected = checked;
                //$('button').removeAttr('disabled');
            }else{
                //$('button').attr('disabled','disabled');
            }
        }, true ); 
		
		$scope.isAll = false;
        $scope.selectAllNotes = function() {
            if($scope.isAll === false) {
                angular.forEach($scope.usernotes, function(input){
                    input.checked = true;
                });
                $scope.isAll = true;
            } else {
                angular.forEach($scope.usernotes, function(input){
                    input.checked = false;
                });
                $scope.isAll = false;
            }
        };
		
	});
	
	app.controller('addeditCtrl', function ($scope, $rootScope, $location, $routeParams, Data, usernote) {
		var noteId = ($routeParams.noteId) ? parseInt($routeParams.noteId) : 0;
		$rootScope.htitle = (noteId > 0) ? 'Edit Note' : 'Add New Note';
		$scope.buttonText = (noteId > 0) ? 'Update Note' : 'Add New Note';
		
		var original = usernote;
			original._id = noteId;
			original.priority = parseInt(usernote.priority,10);
		$scope.usernote = angular.copy(original);
		$scope.usernote._id = noteId;

		if(original){
			var texCount = original.notes.length;
			$scope.strcount = texCount;
		}

		$scope.isClean = function() {
			return angular.equals(original, $scope.usernote);
		}

		$scope.deleteNote = function(usernote) {
			var confirms = confirm("Are you sure to delete note number: "+$scope.usernote._id);
			if(confirms==true){
				$location.path('/dashboard');
				Data.delete('deleteNote',{
					params:{
						id:$scope.usernote._id
					}
				}).then(function (results) {
					$scope.resp = results;
				});
			}			
		};

		$scope.addeditNotes = function(usernote) {
			$location.path('/dashboard');
			if (noteId <= 0) {
				usernote.uid = localStorage.uid;
				Data.get('insertNote',{
					params:usernote
				}).then(function (results) {
					$scope.resp = results;
				});
			} else {
				Data.get('updateNote',{
					params:usernote
				}).then(function (results) {
					$scope.usernotes = results;
				});
			}
		}; 
	});
	
	app.controller('naveCtrl', function($scope,$rootScope,$location){
		$scope.logout = function () {
			$rootScope.authenticated = false;
			localStorage.authenticated = false;
			localStorage.uid='';
			$location.path('/login');
		}
		
	});
	
	app.factory("Data", ['$http', function ($http) { 
		// This service connects to our REST API
		var serviceBaseUrl = 'api/';
		var obj = {};
		obj.post = function (q,object) {
			return $http.post(serviceBaseUrl+q, object).then(function (results) {
				return results.data;
			});
		};
		obj.get = function (q,parms) {
			return $http.get(serviceBaseUrl + q, parms).then(function (results) {
				return results.data;
			});
		};
		obj.delete = function (q,parmss) {
            return $http.delete(serviceBaseUrl + q,parmss).then(function (results) {
                return results.data;
            });
        };
		return obj;
	}]);
	
	app.directive('myMaxlength', function() {
		return {
			require: 'ngModel',
			link: function (scope, element, attrs, ngModelCtrl) {
				scope.$watch( attrs.ngModel , function(newVal,oldVal){
					if(newVal.length>300){
					ngModelCtrl.$setViewValue( newVal.substr(0,300));
					ngModelCtrl.$render();
					}
				}, true );
				
			}
		}; 
	});