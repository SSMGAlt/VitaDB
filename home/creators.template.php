<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2 main" ng-controller="creatorsController">
	<div class="row" id="top">
		<ol class="breadcrumb">
			<li>
				<i class="fa fa-code" aria-hidden="true"></i> &nbsp;
				Top 50 developers ranked by releases count
			</li>
		</ol>
		<ol class="breadcrumb">
			<div angular-marquee style="overflow: hidden;white-space:nowrap;">
				<span ng-repeat="entry in updates track by $index"><span ng-if="$index != 0"> - </span><b>{{entry.author}}</b> {{entry.object}} <b>{{entry.hb}}</b> on <b>{{entry.date}} GMT -1:00</b>.</span>
			</div>
		</ol>
	</div>
		<ol class="breadcrumb-alert">
			<b>Note:</b> You can now download homebrews from VitaDB directly from your PSVITA by using <a href="https://www.rinnegatamante.eu/vitadb/#/info/877">VitaDB Downloader</a>.
		</ol>
	<br>
	<div class="row" id="hb-list">
		<div ng-repeat="creator in creators" class="col-xs-12 col-sm-6 col-md-6 col-lg-4">
			<div class="panel panel-widget ">
				<div class="row no-padding">
					<div class="col-md-4">
						<center><a href="#/user/{{creator.name}}"><img class="icon" ng-src="avatars/{{creator.avatar || 'unknown.png'}}" /></a></center>
					</div>
					<div class="col-md-8">
						<h4 style="white-space: nowrap;overflow: hidden;">
							<span ng-if="$index == 0">🥇</span>
							<span ng-if="$index == 1">🥈</span>
							<span ng-if="$index == 2">🥉</span>
							<a href="#/user/{{creator.name}}"><b>{{creator.name}}</b></a>
						</h4>
						<h6>{{creator.ports}} releases</h6>
					</div>
					<div class="topcorner"><h6 style="text-align: right;">#{{$index + 1}}&nbsp;</h6></div>
				</div>
			</div>
		</div>
	</div>
</div>
