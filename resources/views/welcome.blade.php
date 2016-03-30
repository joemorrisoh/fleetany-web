@extends('layouts.default')

@section('content')

<script type="text/javascript">
  google.charts.load('current', {'packages':['bar']});
</script>

<div class="mdl-layout mdl-js-layout mdl-color--grey-100">
	<main class="mdl-layout__content">
	
		@include ('includes.statistics.cardnumber', ['statistics' => $vehiclesStatistics])
		
		@include ('includes.statistics.cardnumber', ['statistics' => $servicesStatistics])
		
		@include ('includes.statistics.cardnumber', ['statistics' => $tripsStatistics])
		
		@include ('includes.statistics.cardbarchart', ['statistics' => $lastsFuelCostStatistics, 
														'x_desc' => 'Mes',
														'y_desc' => 'Custo',
														'name' => 'fuel_cost1',  
		])

		@include ('includes.statistics.cardbarchart', ['statistics' => $lastsFuelCostStatistics, 
														'x_desc' => 'Mes',
														'y_desc' => 'Custo',
														'name' => 'fuel_cost2',  
		])
		
	</main>
</div>

@endsection
