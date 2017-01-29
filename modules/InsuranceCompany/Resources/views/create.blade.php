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
					<h3 class='m-t-none m-b'>Compañia de Seguros</h3>
				</div>
				<div class="ibox-content">
					<div class='row'>
					@if ($edit)
						{!! Form::model($iCompany, 
							array(
								'method' => 'PATCH',
								'route' => array('insurance_company_update',
									$iCompany->id))
							) !!}
					@else
						{!! Form::model($iCompany, 
							array(
								'route' => array('insurance_company_store')
							)) !!}
					@endif
						<div class="form-group">
							{!! Form::label('company_name', 'Nombre de la Compañia') !!}
							{!! Form::text('company_name', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
						</div>
						<div class="form-group">
							{!! Form::label('representative', 'Representante') !!}
							{!! Form::text('representative', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
						</div>
						{{ Form::hidden('createOffice',true) }}
						<div class="form-group">
							<a href="{{ route('insurance_company') }}" 
								class="btn btn-white">Cancelar
							</a>
							@if (Auth::user()->can(['insuranceCompany_edit']))
								{!! Form::submit('Guardar Cambios', ['class' => 'btn btn-success btn-w-m']) !!}
							@endif
						</div>
						@if ($edit)
							{!! Form::hidden('id', $iCompany->id) !!}
						@endif
					{!! Form::close() !!}
					</div>
				</div>
			</div>
		</div>
	</div>
@stop