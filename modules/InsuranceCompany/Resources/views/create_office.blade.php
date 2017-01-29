@extends('layouts.master')

@section('novatech_content')
	@if ($errors->any())
		<div class="alert alert-danger">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			<strong>Hubo un inconveniente!</strong>
			@foreach ($errors->all() as $error)
				<li>{{$error}}</li>
			@endforeach
		</div>
	@endif
	<div class="row">
		<div class="col-lg-10">
			<div class="ibox float-e-margins">
				<div class="ibox-title">
					<h3 class='m-t-none m-b'>Oficina de {{ $iCompany->company_name }}</h3>
				</div>
				<div class="ibox-content">
					<div class='row'>
					@if ($edit)
						{!! Form::model($company_office, 
								array(
									'method' => 'PATCH',
									'route' => array('insurance_company_office_update',
														$iCompany->id,
														$company_office->id)
									  )) !!}
					@else
						{!! Form::model($company_office, 
								array('route' => array('insurance_company_office_store', 
													$iCompany->id)
									  )) !!}
					@endif
						<div class="form-group">
							{!! Form::label('office_name', 'Nombre de la Oficina') !!}
							{!! Form::text('office_name', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
						</div>
						<div class="form-group">
							{!! Form::label('representative', 'Representante Oficina') !!}
							{!! Form::text('representative', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
						</div>
						<div class="form-group">
							{!! Form::label('email', 'Email') !!}
							{!! Form::text('email', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
						</div>
						<div class="form-group">
							{!! Form::label('phone', 'Telefono') !!}
							@if (count($company_office->phones)==0)
								{!! Form::text('phone', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
							@else
								@foreach ($company_office->phones as $phone)
									{!! Form::text('phone', $phone->number, ['class' => 'form-control',$disabled=>$disabled]) !!}
								@endforeach 
							@endif
						</div>
						<div class="form-group">
							{!! Form::label('country', 'Pais') !!}
							{!! Form::text('country', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
						</div>
						<div class="form-group">
							{!! Form::label('state', 'Estado') !!}
							{!! Form::text('state', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
						</div>
						<div class="form-group">
							{!! Form::label('city', 'Ciudad') !!}
							{!! Form::text('city', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
						</div>
						<div class="form-group">
							{!! Form::label('address', 'Direccion') !!}
							{!! Form::text('address', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
						</div>
						<div class="form-group">
							<a href="{{ route('insurance_company') }}" 
								class="btn btn-white">Cancelar
							</a>
							@if (Auth::user()->can(['insuranceCompany_editOffice']))
								{!! Form::submit('Guardar Cambios', ['class' => 'btn btn-success btn-w-m']) !!}
							@endif
							@if (Auth::user()->can(['insuranceCompany_deleteOffice']))
								<a href="#" 
									data-href="{{ route('insurance_company_office_delete',
												['id'=>$iCompany->id,
												 'id_office'=>$company_office->id]) }}" 
									class="btn btn-danger btn-borrar">Eliminar
								</a>
							@endif
						</div>
						
						{!! Form::hidden('managaEmails', true) !!}
						{!! Form::hidden('id', $iCompany->id) !!}
						@if ($edit)
							{!! Form::hidden('id_office', $company_office->id) !!}
						@endif
					{!! Form::close() !!}
					</div>
				</div>
			</div>
		</div>
	</div>
@stop

@section('novatech_headers')
	{!! Html::style('css/plugins/sweetalert/sweetalert.css') !!}
@stop

@section('novatech_scripts')
	<script src="{{ URL::asset('js/plugins/sweetalert/sweetalert.min.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function () {
			$(".btn-borrar").on("click", function (event) {
				event.preventDefault()
				var href = $(this).attr('data-href');
				var token = $("input[name=_token]").val();
				swal({
				  title: "¿Está seguro de eliminar esta oficina?",
				  text: "Está a punto de eliminar una oficina. Esta acción no se puede deshacer.",
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
						    },
						    error: function(result){ 
						        alert(result);
						    }
						});
						window.location.href="{{ route('insurance_company') }}";
					};
				});
			});
		});
	</script>
@stop