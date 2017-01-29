@extends('layouts.master')

@section('novatech_module_title')
    <div class="col-lg-10">
    	<div>
	    	<a href="{{ route('plan_create') }}" 
	    		class="btn btn-primary">  
	    		<i class="fa fa-plus"></i>Create Insurance Plan
			</a>
			<a href="#" 
				class="btn btn-primary">  
	    		<i class="fa fa-plus"></i>Update Price Insurance Plans
			</a>
		</div>
	</div>
@stop

@section('novatech_content')
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
        	<div class="ibox-title">
				Plans
			</div>
            <div class="ibox-content">
            	@if(isset($plan))
            	<div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" id="novatech_table">
						<thead>
							<tr>
								<td>
									<strong>Deducibles</strong>
								</td>
								@foreach($deducibles as $deducible)
									<td >
										<strong>{{ $deducible->name }}</strong>
									</td>
								@endforeach
							</tr>
						</thead>	
						<tbody>

						</tbody>
					</table>
					<table class="table table-striped table-bordered table-hover" id="novatech_table">
						<thead>
							<tr>
								<td>
									<strong>Edad</strong>
								</td>
								@foreach($numberPayments as $payments)
									<td>
										<strong>{{ $payments->name }}</strong>
									</td>
								@endforeach
							</tr>
						</thead>	
						<tbody>	
							@foreach ($plan->planCosts as $costs)
								<tr>
									<td >
										{{ $costs->start_age  }} - {{ $costs->end_age  }}
									</td>
									<td>
										<strong>{{ $costs->value }}</strong>
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
					<input type="hidden" id="novatech_token" value="{{ csrf_token() }}">
					{!! $plan->render() !!}
				</div>
				@endif
            </div>
        </div>
    </div>
@stop

@section('novatech_headers')
	{!! Html::style('css/plugins/sweetalert/sweetalert.css') !!}
	{!! Html::style('css/plugins/dataTables/datatables.min.css') !!}
@stop

@section('novatech_scripts')
	<script src="{{ URL::asset('js/plugins/sweetalert/sweetalert.min.js') }}"></script>
	<script src="{{ URL::asset('js/plugins/dataTables/datatables.min.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function () {
			$('#novatech_table').DataTable();
			$(".btn-borrar").on("click", function (event) {
				event.preventDefault()
				var href = $(this).attr('data-href');
				var token = $("#novatech_token").val();
				swal({
				  title: "¿Está seguro de eliminar este agente ?",
				  text: "Está a punto de eliminar un agente. Esta acción no se puede deshacer.",
				  type: "warning",
				  showCancelButton: true,
				  confirmButtonColor: "#DD6B55",
				  confirmButtonText: "Si, eliminar",
				  cancelButtonText: "No eliminar",
				  closeOnConfirm: true
				},
				function(isConfirm){
					if (isConfirm) {
						$.ajax({
						    url: href,
						    type: 'DELETE',
						    data: { '_token': token},
						    success: function(result) {
						        alert(result)
						        window.location.href="{{ route('plan') }}";
						    },
						    error: function(result){ 
						        alert("Agente cloud not be deleted");
						    }
						});
					};
				});
			});
		});
	</script>
@stop