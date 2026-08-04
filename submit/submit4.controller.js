app.controller('submit4Controller',function($scope, $rootScope, $http, $location, $interval, $css){
	
	$css.removeAll();
	$css.add([
		'templates/lumino/css/styles-' + $rootScope.theme + '.css',
		'css/style-' + $rootScope.theme + '.css',
		'css/vitadb-' + $rootScope.theme + '.css',
	]);
	
	$scope.conf = {}
	$scope.conf.user = $rootScope.user.email
	$scope.conf.password = $rootScope.user.password
	$scope.conf.log_author = $rootScope.user.name
	$scope.conf.uploader = "icon0.html"
	$scope.conf.type = "10"
	$scope.conf.sshot = ""
	if (typeof($rootScope) == 'undefined' || $rootScope.user == undefined || $rootScope.user.role > 2) $location.path("/");
	
	// submit function
	$scope.submit = function () {
		$http.post('submit4.php', $scope.conf).then(() => {
			alertify.success($scope.conf.name + " added successfully!");
			$location.path('/psp')
		})
	}
	
	// Watch for changes caused by the iframe
	var theInterval = $interval(function(){
		if ($scope.conf.sshot != document.getElementById('sshot').value) $scope.conf.sshot = document.getElementById('sshot').value
		if ($scope.conf.titleid != document.getElementById('hb_titleid').value) $scope.conf.titleid = document.getElementById('hb_titleid').value
		if ($scope.conf.name != document.getElementById('hb_title').value) $scope.conf.name = document.getElementById('hb_title').value
		if ($scope.conf.icon != document.getElementById('url').value) $scope.conf.icon = document.getElementById('url').value
	}, 500)
	
	// stop polling on navigate away
	$scope.$on('$destroy', function(){
		$interval.cancel(theInterval)
	})
})
