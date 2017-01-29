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
					<h3 class='m-t-none m-b'>Agente</h3>
				</div>
				<div class="ibox-content">
					<div class='row'>
					@if ($edit)
						{!! Form::model($agente, 
							array(
								'method' => 'PATCH',
								'route' => array('agente_update',
									$agente->id))
							) !!}
					@else
						{!! Form::model($agente, 
							array(
								'route' => array('agente_store')
							)) !!}
					@endif
						<div class="form-group">
							{!! Form::label('name', 'Nombre') !!}
							{!! Form::text('name', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
						</div>
						<div class="form-group">
							{!! Form::label('lastname', 'Apellido') !!}
							{!! Form::text('lastname', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
						</div>
						<div class="form-group">
							{!! Form::label('identity_document', 'Cédula Identidad') !!}
							{!! Form::text('identity_document', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
						</div>
						<div class="form-group" id='nova_agente_dob'>
							{!! Form::label('dob', 'Fecha de Nacimiento') !!}
							<div class="input-group date">
								<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
								{!! Form::text('dob', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
							</div>
						</div>
						<div class="form-group">
							{!! Form::label('email', 'Email') !!}
							{!! Form::text('email', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
						</div>
						<div class="form-group">
							{!! Form::label('skype', 'Skype') !!}
							{!! Form::text('skype', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
						</div>
						<div class="form-group">
							{!! Form::label('mobile', 'Celular') !!}
							{!! Form::text('mobile', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
						</div>
						<div class="form-group">
							{!! Form::label('phone', 'Telefono Fijo') !!}
							{!! Form::text('phone', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
						</div>
						<div class="form-group">
							{!! Form::label('country', 'Pais') !!}
							{!! Form::text('country', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
						</div>
						<div class="form-group">
							{!! Form::label('province', 'Provincia') !!}
							{!! Form::text('province', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
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
							{!! Form::label('comision', 'Comission') !!}
							{!! Form::text('comision', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
						</div>
						<div class="form-group">
							{!! Form::label('subagent', 'Is a Sub-Agente?') !!}
							{!! Form::checkbox('subagent', 1, null, ['class' => 'form-control',$disabled=>$disabled,'id'=>'nova_is_subagent']) !!}
						</div>
						<div class="form-group" id='nova_group_leader'>
							{!! Form::label('leader', 'Leader') !!}
							{!! Form::select('leader', 
									$agents_list, 
									[
									  	'class' => ['form-control,chosen-select'],
									  	$disabled=>$disabled
									]) !!}
						</div>

						<div class="form-group">
							<a href="{{ route('agente') }}" 
								class="btn btn-white">Cancelar
							</a>
							@if ($edit)
									{!! Form::submit('Guardar Cambios', ['class' => 'btn btn-success btn-w-m']) !!}
							@else
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

@section('novatech_headers')
	{!! Html::style('css/plugins/datapicker/datepicker3.css') !!}
	{!! Html::style('css/plugins/chosen/chosen.css') !!}
@stop

@section('novatech_scripts')
	<script src="{{ URL::asset('js/plugins/datapicker/bootstrap-datepicker.js') }}"></script>
	<script src="{{ URL::asset('js/plugins/chosen/chosen.jquery.js') }}"></script>
	<script type="text/javascript">
		$(document).ready(function () {
			$('#nova_agente_dob .input-group.date').datepicker({
                startView: 2,
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                autoclose: true,
                format: "dd/mm/yyyy"
            });
            var config = {
                '.chosen-select'           : {},
                '.chosen-select-deselect'  : {allow_single_deselect:true},
                '.chosen-select-no-single' : {disable_search_threshold:10},
                '.chosen-select-no-results': {no_results_text:'Oops, nothing found!'},
                '.chosen-select-width'     : {width:"95%"}
                }
            for (var selector in config) {
                $(selector).chosen(config[selector]);
            }
            //TODO: hide the leader field is the subagent field is 0
            if($("#nova_is_subagent").is(':checked')){
            	$("#nova_group_leader").show();
            }else{
            	$("#nova_group_leader").hide();
            }

            $('#nova_is_subagent').click(function() {
			    $("#nova_group_leader").toggle(this.checked);
			});
            
		});
	</script>
@endsection
