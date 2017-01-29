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
					<h3 class='m-t-none m-b'>Plan de Seguro</h3>
				</div>
				<div class="ibox-content">
					<div class='row'>
					@if ($edit)
						{!! Form::model($plan, 
							array(
								'method' => 'PATCH',
								'route' => array('plan_update',
									$plan->id))
							) !!}
					@else
						{!! Form::model($plan, 
							array(
								'route' => array('plan_store')
							)) !!}
					@endif
						<div class="form-group">
							{!! Form::label(
									'insurance_company_id', 'Compañia de Seguros'
								) !!}
							{!! Form::select(
									'insurance_company_id', $insurance_company, null, 
										['class' => 'form-control',
										$disabled=>$disabled]
								) !!}
						</div>
						<div class="form-group">
							{!! Form::label('name', 'Plan Name') !!}
							{!! Form::text('name', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
						</div>
						<div class="form-group">
							{!! Form::label('description', 'Description') !!}
							{!! Form::text('description', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
						</div>
						{{ Form::hidden('createDeducibles',true) }}
						<div class="form-group">
							<a href="{{ route('plan') }}" 
								class="btn btn-white">Cancelar
							</a>
							@if (Auth::user()->can(['plan_edit']))
								{!! Form::submit('Guardar Cambios', ['class' => 'btn btn-success btn-w-m']) !!}
							@endif
						</div>
					{!! Form::close() !!}
					</div>
				</div>
			</div>
		</div>
	</div>
@stop