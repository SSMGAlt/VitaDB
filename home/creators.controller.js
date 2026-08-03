app.controller('creatorsController',function($scope, $rootScope, $http, $css){
	
	$css.removeAll();
	$css.add([
		'templates/lumino/css/styles-' + $rootScope.theme + '.css',
		'css/style-' + $rootScope.theme + '.css',
		'css/vitadb-' + $rootScope.theme + '.css',
	]);
	
	$scope.creators = []
	
	$http.post('get_top_developers.php').then(function(res){
		$scope.creators = res.data
	})
	
})
