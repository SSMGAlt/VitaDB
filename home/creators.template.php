<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2 main" ng-controller="creatorsController">
	<div class="row" id="top">
		<ol class="breadcrumb">
			<li>
				<i class="fa fa-code" aria-hidden="true"></i> &nbsp;
				Top 50 Developers (ranked by Game Ports made)
			</li>
		</ol>
	</div>
	<br>
	<div class="row" id="hb-list">
		<div class="col-xs-12">
			<div class="fixed-table-container">
				<table data-toggle="table" class="table table-hover">
					<thead>
						<tr>
							<th>#</th>
							<th>Developer</th>
							<th>Ports made</th>
						</tr>
					</thead>
					<tbody>
						<tr ng-repeat="creator in creators">
							<td>{{$index + 1}}</td>
							<td><a href="#/user/{{creator.name}}">{{creator.name}}</a></td>
							<td>{{creator.ports}}</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
