app.controller('submit2Controller',function($scope, $rootScope, $http, $location, $css, $interval){
	
	$css.removeAll();
	$css.add([
		'templates/lumino/css/styles-' + $rootScope.theme + '.css',
		'css/style-' + $rootScope.theme + '.css',
		'css/vitadb-' + $rootScope.theme + '.css',
	]);
	
	$scope.conf = {}
	$scope.conf.sshot = ""
	$scope.conf.user = $rootScope.user.email
	$scope.conf.password = $rootScope.user.password
	$scope.conf.log_author = $rootScope.user.name
	if (typeof($rootScope) == 'undefined' || $rootScope.user == undefined || $rootScope.user.role > 2) $location.path("/");
	
	// submit function
	$scope.submit = function () {
		$http.post('submit2.php', $scope.conf).then(() => {
			alertify.success($scope.conf.name + " added successfully!");
			$location.path('/plugins')
		})
	}
	
	// Watch for changes caused by the iframe
	var theInterval = $interval(function(){
		if ($scope.conf.sshot != document.getElementById('sshot').value) $scope.conf.sshot = document.getElementById('sshot').value
	}, 500)
	
	// stop polling on navigate away
	$scope.$on('$destroy', function(){
		$interval.cancel(theInterval)
	})
})