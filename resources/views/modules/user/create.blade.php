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
		<div class="col-lg-5">
			<div class="ibox float-e-margins">
				<div class="ibox-title">
					Nuevo usuario
				</div>
				<div class="ibox-content">
					{!! Form::open(['url' => 'user/store']) !!}
						<div class="form-group">
							{!! Form::label('name', 'Nombres') !!}
							{!! Form::text('name', null, ['class' => 'form-control']) !!}
						</div>
						<div class="form-group">
							{!! Form::label('lastname', 'Apellidos') !!}
							{!! Form::text('lastname', null, ['class' => 'form-control']) !!}
						</div>
						<div class="form-group">
							{!! Form::label('email', 'Correo electrónico') !!}
							{!! Form::text('email', null, ['class' => 'form-control']) !!}
						</div>
						<div class="form-group">
							{!! Form::label('password', 'Contraseña') !!}
							{!! Form::password('password', ['class' => 'form-control']) !!}
						</div>
						<div class="form-group">
							{!! Form::label('confirm_password', 'Confirmar la contraseña') !!}
							{!! Form::password('confirm_password', ['class' => 'form-control']) !!}
						</div>
						<div class="form-group">
							{!! Form::submit('Crear usuario', ['class' => 'btn btn-success btn-w-m']) !!}
						</div>
					{!! Form::close() !!}
				</div>
			</div>
		</div>
	</div>
@stop