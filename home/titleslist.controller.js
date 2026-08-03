app.controller('titleslistController',function ($scope, $rootScope, $http, $routeParams, $location, $anchorScroll, $css){
	
	$css.removeAll();
	$css.add([
		'templates/lumino/css/styles-' + $rootScope.theme + '.css',
		'css/style-' + $rootScope.theme + '.css',
		'css/vitadb-' + $rootScope.theme + '.css',
	]);
	
	$scope.brews = {}
	var data = {
		hid: $routeParams.hid,
	}
	
	$http.post('list_hbs_by_titleid.php', data).then(function(res){
		$scope.brews = res.data
		var j = 0
		while (j < $scope.brews.length){
			$scope.brews[j].authors = $scope.brews[j].author.split(" & ")
			$scope.brews[j].authors_info = []
			var i = 0
			while (i < $scope.brews[j].authors.length){
				$scope.brews[j].authors_info.push({
					name : $scope.brews[j].authors[i],
					paypal : "",
					bitcoin : "",
					patreon : ""
				})
				i++
			}
			j++
		}
	})
	
})
