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
  
	app.controller('dashboardCtrl', function ($scope, $rootScope, $route, Data, $filter, popupService) {
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
		});
		
		$scope.deleteNote = function(usernote) {
			popupService.confirmPopup({
				title:'Confirm Delete',
				message:'Are you sure to delete note number: '+usernote.id,
				paramsCb:['aijaz','ahmad'],
				callbackFun:function(){
					Data.delete('deleteNote',{
						params:{
							id:usernote.id
						}
					}).then(function (results) {
						$scope.resp = results;
						$scope.usernotes.splice($scope.usernotes.indexOf(usernote),1);
					});
				},
				paramsCan:['can-aijaz','can-ahmad'],
				canFun:function(){
					console.log(this.paramsCan);
				}
			});
		};
		
		$scope.deleteSelected = function() {
			var selectIds="";
			for(i in $scope.selected){
				selectIds += $scope.selected[i]['id']+",";
			}
			selectIds = selectIds.slice(0,-1);
			popupService.confirmPopup({
				title:'Confirm Delete',
				message:'Are you sure to delete note number: '+selectIds,
				paramsCb:[],
				callbackFun:function(){
					Data.delete('deleteNote',{
						params:{
							id:selectIds
						}
					}).then(function (results) {
						$scope.resp = results;
						for(i in $scope.selected){
							$scope.usernotes.splice($scope.usernotes.indexOf($scope.selected[i]),1);
						}
					});
				},
				paramsCan:[],
				canFun:function(){
					console.log(this.paramsCan);
				}
			});
			/* var confirms = confirm("Are you sure to delete note numbers: "+selectIds);
			if(confirms==true){
				$route.reload();
				Data.delete('deleteNote',{
					params:{
						id:selectIds
					}
				}).then(function (results) {
					$scope.resp = results;
				});
			} */
		}
		
		$scope.$watch( "usernotes" , function(n,o){
            var checked= $filter("filter")( n , {checked:true} );
            if(checked){
                $scope.selected = checked;
            }
        }, true ); 
		/* $scope.isAll = false;
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
        }; */
		
	});
	
	app.controller('addeditCtrl', function ($scope, $rootScope, $location, $routeParams, Data, usernote, popupService) {
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
			popupService.confirmPopup({
				title:'Confirm Delete',
				message:'Are you sure to delete note number: '+usernote._id,
				paramsCb:[],
				callbackFun:function(){
					Data.delete('deleteNote',{
						params:{
							id:usernote._id
						}
					}).then(function (results) {
						$scope.resp = results;
						$location.path('/dashboard');
					});
				},
				paramsCan:[],
				canFun:function(){
					console.log(this.paramsCan);
				}
			});
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
	
	app.directive('backTop', function() {
		return {
			restrict: 'AE',
			transclude: true,
			template: '<div id="backtop" class="{{theme}}">{{text}}<div ng-transclude></div></div>',
			scope: {
			  text: "@buttonText",
			  speed: "@scrollSpeed"
			},
			link: function(scope, element) {
				scope.text = scope.text || 'Scroll top';
				scope.speed = parseInt(scope.speed, 10) || 300;
				element.on('click', function() {
				var takingTime = Math.round((element.offset().top/scope.speed)*500);
					$("body").animate({scrollTop: 0}, takingTime);
				});
				window.addEventListener('scroll', function() {
					if (window.pageYOffset > 0) {
						element.addClass('show');
					} else {
						element.removeClass('show');
					}
				});
			}
		}; 
	});
	
	app.factory("popupService",function($document, $compile, $rootScope, $templateCache){
		var body = $document.find('body');
			return {
				confirmPopup: function (data) {
					var scope = $rootScope.$new();
					angular.extend(scope, data);
					scope.title = data.title ? data.title : 'Alert!!!';
					scope.message = data.message ? data.message : 'No message';
					scope.closeButtonText = data.closeButtonText ? data.closeButtonText : 'Cancle';
					scope.actionButtonText = data.actionButtonText ? data.actionButtonText : 'Delete';

					scope.donefunction = data.callbackFun;
					scope.canfunction = data.canFun;
					var confirmPopupStr = angular.element([
                        '<div class="modal" style="display: block;">',
							'<div class="modal-dialog">',
								'<div class="modal-content">',
									'<div class="modal-header">',
										'<button type="button" class="close" data-dismiss="modal" aria-hidden="true" ng-click="close();">×</button>',
										'<h4 class="modal-title" id="myModalLabel">{{title}}</h4>',
									'</div>',
									'<div class="modal-body">',
										'<p>{{message}}</p>',
										'<p>Do you want to proceed?</p>',
									'</div>',
									'<div class="modal-footer">',
										'<button type="button" class="btn btn-default" data-dismiss="modal" ng-click="close();">{{closeButtonText}}</button>',
										'<button type="button" class="btn btn-danger btn-ok" ng-click="ok();">{{actionButtonText}}</button>',
									'</div>',
								'</div>',
							'</div>',
						'</div>'
                    ].join(''));
					$compile(confirmPopupStr)(scope);
                    body.append(confirmPopupStr);					
					
					scope.close = function () {
						confirmPopupStr.remove();
						scope.$destroy();
					};
					
					scope.ok = function(){
						if(scope.donefunction){
							scope.donefunction();
							confirmPopupStr.remove();
						}else{
							confirmPopupStr.remove();
						}
					}
					scope.cancle = function(){
					if(typeof scope.canfunction != 'undefined'){
							scope.canfunction();
							confirmPopupStr.remove();
						}else{
							confirmPopupStr.remove();
							scope.$destroy();
						}
					}
				}
			}
		});
	
	
	
	
	
	
	