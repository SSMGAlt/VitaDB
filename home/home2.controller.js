app.controller('home2Controller',function($scope, $rootScope, $http, $routeParams, $location, $anchorScroll, $css){
	
	$css.removeAll();
	$css.add([
		'templates/lumino/css/styles-' + $rootScope.theme + '.css',
		'css/style-' + $rootScope.theme + '.css',
		'css/vitadb-' + $rootScope.theme + '.css',
	]);
	
	$scope.field = ''
	$scope.sort_filter = "0"
	$scope.sort_by = "-date"
	$scope.changeSort = function () {
		switch (Number($scope.sort_filter)){
			case 1:
				$scope.sort_by = "date"
				break;
			case 2:
				$scope.sort_by = "-downloads"
				break;
			case 3:
				$scope.sort_by = "downloads"
				break;
			default:
				$scope.sort_by = "-date"
				break;
		}
	}

	$http.post('list_plugins_json.php').then(function(res){
		$scope.brews = res.data
		for (var i=0;i<res.data.length;i++){
			$scope.brews[i].authors = $scope.brews[i].author.split(" & ")
		}
	})
	
	$http.post('get_last_updates.php').then(function(res){
		$scope.updates = res.data
	})
	
	$scope.goTop = function(){
		$location.hash('top');
		$anchorScroll();
	}
	
	// Angular filter
	$scope.filterBrews = function(val){
		return function(brew){
			if (val == undefined) return true;
			return brew.author.toLowerCase().indexOf(val.toLowerCase() || '') !== -1 || brew.name.toLowerCase().indexOf(val.toLowerCase() || '') !== -1 || brew.description.toLowerCase().indexOf(val.toLowerCase() || '') !== -1;
		}
	}
	
})
