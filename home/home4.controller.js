app.controller('home4Controller',function($scope, $rootScope, $http, $routeParams, $location, $anchorScroll, $css){
	
	$css.removeAll();
	$css.add([
		'templates/lumino/css/styles-' + $rootScope.theme + '.css',
		'css/style-' + $rootScope.theme + '.css',
		'css/vitadb-' + $rootScope.theme + '.css',
	]);
	
	$scope.field = ''
	$scope.cat_filter = "0"
	$scope.sortOptions = [
		{value: "-date", label: "Most Recent"},
		{value: "date", label: "Oldest"},
		{value: "-downloads", label: "Most Downloaded"},
		{value: "downloads", label: "Least Downloaded"}
	]
	$scope.sort_by = $scope.sortOptions[0].value
	$scope.updates = []
	$scope.views = []
	// PSP genres live at type 10-13 (Original Game/Game Port/Utility/Emulator),
	// mirroring how Vita homebrews use 1/2/4/5 - kept in their own range so
	// they don't collide with Plugins (8) or PC Tools (9)
	$scope.views[10] = []
	$scope.views[11] = []
	$scope.views[12] = []
	$scope.views[13] = []

	$http.post('list_psp_json.php').then(function(res){
		$scope.brews = res.data
		for (var i=0;i<res.data.length;i++){
			$scope.brews[i].authors = $scope.brews[i].author.split(" & ")
			switch (Number(res.data[i].type)){
				case 10:
					$scope.brews[i].genre = "Original Game"
					break;
				case 11:
					$scope.brews[i].genre = "Game Port"
					break;
				case 12:
					$scope.brews[i].genre = "Utility"
					break;
				case 13:
					$scope.brews[i].genre = "Emulator"
					break;
				default:
					$scope.brews[i].genre = "Unknown"
					break;
			}
			$scope.views[Number(res.data[i].type)].push($scope.brews[i])
		}
		$scope.views[0] = $scope.brews
	})
	
	$http.post('get_last_updates.php').then(function(res){
		$scope.updates = res.data
	})
	
	$scope.goTop = function(){
		$location.hash('top');
		$anchorScroll();
	}
	
	$scope.changeView = function () {
		$scope.brews = $scope.views[Number($scope.cat_filter)]
	}
	
	// Angular filter
	$scope.filterBrews = function(val){
		return function(brew){
			if (val == undefined) return true;
			return brew.author.toLowerCase().indexOf(val.toLowerCase() || '') !== -1 || brew.name.toLowerCase().indexOf(val.toLowerCase() || '') !== -1 || brew.description.toLowerCase().indexOf(val.toLowerCase() || '') !== -1;
		}
	}
	
})
