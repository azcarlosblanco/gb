@extends('layouts.master')

@section('novatech_module_title')
    <div class="col-lg-10">
    	<div>
	    	<a href="{{ route('agente_form') }}" 
	    		class="btn btn-primary">  
	    		<i class="fa fa-plus"></i>Create
			</a>
			<a href="{{ route('agente_rellocate_subagents') }}" 
				class="btn btn-primary">  
	    		<i class="fa fa-plus"></i>Reasign
			</a>
		</div>
	</div>
@stop

@section('novatech_content')
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
        	<div class="ibox-title">
				Agentes
			</div>
            <div class="ibox-content">
            	<div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" id="novatech_table">
						<thead>
							<tr>
								<td><strong>Nombre</strong></td>
								<td><strong>Cédula</strong></td>
								<td><strong>Email</strong></td>
								<td><strong>Celular</strong></td>
								<td><strong>Teléfono Fijo</strong></td>
								<td><strong>Category</strong></td>
								<td><strong>Skype</strong></td>
								
									<td><strong>Editar</strong></td>
									<td><strong>Ver</strong></td>
									<td><strong>Eliminar</strong></td>
							</tr>
						</thead>	
						<tbody>	
							@foreach ($agentes as $agente)
								<tr>
									<td>{{ $agente->full_name  }}</td>
									<td>{{ $agente->identity_document }}</td>
									<td>{{ $agente->email }}</td>
									<td>{{ $agente->mobile }}</td>
									<td>{{ $agente->phone }}</td>
									<td>
										@if($agente->subagent==1)
											{{ 'Subagente' }}
										@else
											{{ 'Agente' }}
										@endif
									</td>
									<td>{{ $agente->skype }}</td>
									<td>
										<a href="{{ route('agente_view',
														['id'=>$agente->id] ) }}" 
											class="btn btn-primary btn-xs">
											<i class="fa fa-pencil"></i>&nbsp;&nbsp;Editar
										</a>
									</td>
									<td>
											<a href="#" 
												data-href="{{ route('agente_delete', 
													['id'=>$agente->id]) }}" 
												class="btn btn-danger btn-xs btn-borrar"><i class="fa fa-minus"></i>&nbsp;&nbsp;Eliminar
											</a>
									</td>
									<input type="hidden" id="novatech_token" value="{{ csrf_token() }}">
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
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
						        window.location.href="{{ route('agente') }}";
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