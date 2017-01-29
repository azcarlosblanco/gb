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
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				Emails para {{ $iCompany->name }}
			</div>
			<div class="ibox-content">
				<div class='row' id='nova_icompany_emails'>
				{!! Form::model(null, ['method' => 'POST', 'action' => ['\Modules\InsuranceCompany\Http\Controllers\InsuranceCompanyOfficeController@manageEmails', $iCompany->id], 'class' => 'form-inline']) !!}
					<div class="form-group">
						{!! Form::submit('Guardar Cambios', ['class' => 'btn btn-success btn-w-m']) !!}
					</div>
					@if (count($emailsComp)==0)
						<div id='entry0'>
							<div class="form-group">
								{!! Form::label('email0', 'Email', ['class'=>'sr-only']) !!}
								{!! Form::text('email0', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
							</div>
							<div class="form-group">
								{!! Form::label('contact_name0', 'Nombre Contacto', ['class'=>'sr-only']) !!}
								{!! Form::text('contact_name0', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
							</div>
							<div class="form-group">
								{!! Form::label('reason0', 'Motivo' , ['class'=>'sr-only']) !!}
								{!! Form::select('reason0', $reason, null, ['class' => 'form-control',$disabled=>$disabled]) !!}
							</div>
							<div class="form-group">
								<a href="#"  id='#nova_delete_entry' class="btn btn-danger btn-xs btn-borrar"><i class="fa fa-minus"></i>&nbsp;&nbsp;Eliminar</a>
							</div>
						</div>
					@else
						@foreach ($emailsComp as $key => $emailC)
							<div id='email'{{ $key }}>
								<div class="form-group">
									{!! Form::label('email'.$key, 'Email', ['class'=>'sr-only']) !!}
									{!! Form::text('email'.$key, $emailC->email, ['class' => 'form-control',$disabled=>$disabled]) !!}
								</div>
								<div class="form-group">
									{!! Form::label('contact_name'.$key, 'Nombre Contacto', ['class'=>'sr-only']) !!}
									{!! Form::text('contact_name'.$key, $emailC->contact_name, ['class' => 'form-control',$disabled=>$disabled]) !!}
								</div>
								<div class="form-group">
									{!! Form::label('reason'.$key, 'Motivo' , ['class'=>'sr-only']) !!}
									{!! Form::select('reason'.$key, $reason, $emailC->contact_name, ['class' => 'form-control',$disabled=>$disabled]) !!}
								</div>
								<div class="form-group">
									<a href="#" class="btn btn-danger btn-xs btn-borrar nova_delete_entry"><i class="fa fa-minus"></i>&nbsp;&nbsp;Eliminar</a>
								</div>
							</div>
						@endforeach
					@endif
					<div class="form-group">
						<a href="#"  id='#nova_add_entry' class="btn btn-create btn-xs "><i class="fa fa-plus"></i>&nbsp;&nbsp;Añadir Cooreo</a>
					</div>
					{!! Form::hidden('lastkey',count($emailsComp),['id'=>'lastkey']) !!}
				{!! Form::close() !!}
				</div>
			</div>
		</div>
	</div>
@stop
@section('novatech_scripts')
	<script type="text/javascript">
		$(document).ready(function () {
			var i=$("#lastkey").val();
	    	
	    	//añadir nuevo email
	    	$("#nova_add_entry").click(function(){
	    		var new_email_div=
	      		$('#nova_icompany_emails').append('new_email_div');
	  		});

	    	//eliminar entry
	     	$(".nova_delete_entry").click(function(){
	     		$( this ).parent().parent().remove();
		 	});
		});
	</script>
@endsection