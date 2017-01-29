@extends('layouts.master')

@section('novatech_module_title')
    <div class="col-lg-10">
    	<div>
	    	<a href="{{ action('\Modules\InsuranceCompany\Http\Controllers\InsuranceCompanyController@create') }}" class="btn btn-primary">  
	    		<i class="fa fa-plus"></i>Create
			</a>
		</div>
	</div>
@stop

@section('novatech_content')
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
        	<div class="ibox-title">
				Insurance Companies
			</div>
            <div class="ibox-content">
            	<div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" id="novatech_table">
						<thead>
							<tr>
								<td><strong>Name</strong></td>
								<td><strong>Representative</strong></td>
								<td><strong>Offices</strong></td>
								@if (Auth::user()->can(['insuranceCompany_edit']))
									<td><strong>Editar</strong></td>
								@else
									<td><strong>Ver</strong></td>
								@endif
								<td><strong>Manage Emails</strong></td>
								@if (Auth::user()->can(['insuranceCompany_createOffice']))
									<td><strong>Añadir Oficina</strong></td>
								@endif
								@if (Auth::user()->can(['insuranceCompany_delete']))
									<td><strong>Eliminar</strong></td>
								@endif
							</tr>
						</thead>	
						<tbody>	
							@foreach ($insurancecompanies as $company)
								<tr>
									<td>{{ $company->company_name  }}</td>
									<td>{{ $company->representative }}</td>
									<td>
										@foreach ( $company->offices as $office)
											<a href="{{ route('insurance_company_office_view',['id'=>$company->id,
											       'id_office'=>$office->id] ) }}" 
												class="btn btn-outline btn-info btn-xs">
													{{ $office->office_name }} 
											</a>
										@endforeach
									</td>
									<td>
										<a href="{{ route('insurance_company_view',
														['id'=>$company->id] ) }}" 
											class="btn btn-primary btn-xs">
											<i class="fa fa-pencil"></i>&nbsp;&nbsp;Editar
										</a>
									</td>
									<td>
										<a href="{{ route('insurance_company_emails_view', 
														['id'=>$company->id]) }}" 
											class="btn btn-primary btn-xs">
											<i class="fa fa-pencil"></i>&nbsp;&nbsp;Emails
										</a>
									</td>
									<td>
										@if (Auth::user()->can(['insuranceCompany_createOffice']))
											<a href="{{ route('insurance_company_office_create',
												['id'=>$company->id]) }}" 
											class="btn btn-primary btn-xs">
											<i class="fa fa-pencil"></i>&nbsp;&nbsp;Añadir Oficina
											</a>
										@endif
									</td>
									<td>
										@if (Auth::user()->can(['insuranceCompany_delete']))
											<a href="#" 
												data-href="{{ route('insurance_company_delete', 
													['id'=>$company->id]) }}" 
												class="btn btn-danger btn-xs btn-borrar"><i class="fa fa-minus"></i>&nbsp;&nbsp;Eliminar
											</a>
										@endif
									</td>
									<input type="hidden" id="novatech_token" value="{{ csrf_token() }}">
								</tr>
							@endforeach
						</tbody>
					</table>
					{!! $insurancecompanies->render() !!}
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
				  title: "¿Está seguro de eliminar esta compania de seguros?",
				  text: "Está a punto de eliminar un usuario. Esta acción no se puede deshacer.",
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
						        window.location.href="{{ route('insurance_company') }}";
						    },
						    error: function(result){ 
						        alert("Compania de Seguros can not be deleted");
						    }
						});
					};
				});
			});
		});
	</script>
@stop