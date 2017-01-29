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
	<div class="col-lg-12">
		<div class="ibox float-e-margins">
			<div class="ibox-title">
				<h3 class='m-t-none m-b'>{{ $plan->name }} Deducibles</h3>
			</div>
			<div class="ibox-content">
				{!! Form::model(null, 
					[
						'route' => array('plan_deducibles', 
										$plan->id),
						'class' => 'form-horizontal',
						'id'    =>'nova_insurance_deducible'
					]
			  			) !!}
					{!! Form::hidden('lastkey',count($plan->deducibles),
						['id'=>'lastkey']) 
					!!}
					<div class="form-group">
				        <label class="col-xs-1 control-label">Deducible</label>
				        <div class="col-xs-4">
				            {!! Form::select('deducible[0][plan_deducible_id]',
				        		 	$plan->deducibles,
				        		 	null,
				        		 	['class'=>'form-control',
				        		 	'placeholder'=>'Option'],
				        		 	) !!}
				        </div>
				        <div class="col-xs-3">
				        	{!! Form::select('deducible[0][reason]',
				        		 	$deducibles_opt,
				        		 	null,
				        		 	['class'=>'form-control',
				        		 	'placeholder'=>'choose reason'],
				        		 	) !!}
				        </div>
				        <div class="col-xs-3">
				            {!! Form::text('deducible[0][cost]', null, ['class'=>'form-control']) !!}
				        </div>
				        <div class="col-xs-1">
				            <button type="button" class="btn btn-default addButton"><i class="fa fa-plus"></i></button>
				        </div>
				    </div>

					@foreach ($plan->deducibles->options as $key => $deducible_option)

					    <div class="form-group">
					        <div class="col-xs-4 col-xs-offset-1">
					        	{!! Form::select(
					        			"deducible[{{ $key+1 }}][plan_deducible_id]",
					        			 $plan->deducibles, 
					        			 $deducible_option->deducible->name, 
					        			 ['class' => 'form-control']) !!}
					        </div>
					        <div class="col-xs-3">
					        	{!! Form::select(
					        			"deducible[{{ $key+1 }}][reason]",
					        			 $deducibles_reasons, 
					        			 $deducible_option->reason, 
					        			 ['class' => 'form-control']) !!}
					        </div>
					        <div class="col-xs-3">
					        	<input type="text" class="form-control" name="deducible[{{ $key+1 }}][cost]" placeholder="Value" value="{{ $deducible_option->cost }}"/>
					        </div>
					        <div class="col-xs-1">
				            	<button type="button" class="btn btn-default removeButton"><i class="fa fa-minus"></i></button>
			            	</div>
					    </div>
					@endforeach

				    <!-- The template for adding new field -->
				    <div class="form-group hide" id="novaTemplate">
				        <div class="col-xs-4 col-xs-offset-1">
				        	{!! Form::select('plan_deducible_id', $plan->deducibles, null, ['class' => 'form-control']) !!}
				        </div>
				        <div class="col-xs-3">
				            {!! Form::select('reason', $deducibles_reasons, null, ['class' => 'form-control']) !!}
				        </div>
				        <div class="col-xs-3">
				        	{!! Form::text('cost', null, ['class'=>'form-control']) !!}
				        </div>
				        <div class="col-xs-1">
				            <button type="button" class="btn btn-default removeButton"><i class="fa fa-minus"></i></button>
				        </div>
				    </div>

				    <div class="form-group">
				        <div class="col-xs-5 col-xs-offset-1">
				        	<a href="{{ route('insurancecompany') }}" 
								class="btn btn-white">Cancelar
							</a>
				        	@if (Auth::user()->can(['insuranceCompany_deleteOffice']))
				            	<button type="submit" class="btn btn-success btn-w-m">Guardar Cambios</button>
				            @endif
				        </div>
				    </div>
				{!! Form::close() !!}
			</div>
		</div>
	</div>
@endsection

@section('novatech_scripts')
<script>
	$(document).ready(function() {
	    index = $("#lastkey").val();
	    $('#nova_insurance_deducible')
	        // Add button click handler
	        .on('click', '.addButton', function() {
	            emailIndex++;
	            var $template = $('#novaTemplate'),
	                $clone    = $template
	                                .clone()
	                                .removeClass('hide')
	                                .removeAttr('id')
	                                .attr('data-index', index)
	                                .insertBefore($template);

	            // Update the name attributes
		            $clone
		                .find('[name="plan_deducible_id"]').attr('name', 'deducible[' + index + '][plan_deducible_id]').end()
		                .find('[name="reason"]').attr('name', 'deducible[' + index + '][reason]').end()
		                .find('[name="cost"]').attr('name', 'deducible[' + index + '][cost]').end();
	        })

	        // Remove button click handler
	        .on('click', '.removeButton', function() {
	            var $row  = $(this).parents('.form-group'),
	                index = $row.attr('data-index');
	            // Remove element containing the fields
	            $row.remove();
	        });
	});
</script>
@endsection