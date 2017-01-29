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
				Emails para {{ $iCompany->name }}
			</div>
			<div class="ibox-content">
				{!! Form::model(null, 
						['method' => 'POST', 
						'action' => ['\Modules\InsuranceCompany\Http\Controllers\InsuranceCompanyController@manageEmails', $iCompany->id], 
						'class' => 'form-horizontal', 
						'id'=>'nova_insurance_email']) !!}
					{!! Form::hidden('lastkey',count($emailsComp),['id'=>'lastkey']) !!}

					<div class="form-group">
				        <label class="col-xs-1 control-label">Email</label>
				        <div class="col-xs-4">
				            <input type="text" class="form-control" name="iEmail[0][email]" placeholder="Email" />
				        </div>
				        <div class="col-xs-4">
				            <input type="text" class="form-control" name="iEmail[0][contact_name]" placeholder="Nombre del Contacto" />
				        </div>
				        <div class="col-xs-2">
				            <input type="text" class="form-control" name="iEmail[0][reason]" placeholder="Motivo" />
				        </div>
				        <div class="col-xs-1">
				            <button type="button" class="btn btn-default addButton"><i class="fa fa-plus"></i></button>
				        </div>
				    </div>

					@foreach ($emailsComp as $key => $emailC)
						<label class="col-xs-1 control-label">Email</label>
					    <div class="form-group">
					        <div class="col-xs-4">
					            <input type="text" class="form-control" name="iEmail[{{ $key+1}}][email]" placeholder="Email" value="{{ $emailC->email }}"/>
					        </div>
					        <div class="col-xs-4">
					            <input type="text" class="form-control" name="iEmail[{{ $key+1 }}][contact_name]" placeholder="Nombre del Contacto" value="{{ $emailC->contact_name }}"/>
					        </div>
					        <div class="col-xs-2">
					            <input type="text" class="form-control" name="iEmail[{{ $key+1 }}][reason]" placeholder="Motivo" value="{{ $emailC->reason }}"/>
					        </div>
					    </div>
					@endforeach

				    <!-- The template for adding new field -->
				    <div class="form-group hide" id="emailTemplate">
				    	<label class="col-xs-1 control-label">Email</label>
				        <div class="col-xs-4 col-xs-offset-1">
				            <input type="text" class="form-control" name="email" placeholder="Email" />
				        </div>
				        <div class="col-xs-4">
				            <input type="text" class="form-control" name="contact_name" placeholder="Nombre Contacto" />
				        </div>
				        <div class="col-xs-2">
				            <input type="text" class="form-control" name="reason" placeholder="Motivo" />
				        </div>
				        <div class="col-xs-1">
				            <button type="button" class="btn btn-default removeButton"><i class="fa fa-minus"></i></button>
				        </div>
				    </div>

				    <div class="form-group">
				        <div class="col-xs-5 col-xs-offset-1">
				            <button type="submit" class="btn btn-success btn-w-m">Submit</button>
				        </div>
				    </div>
				</form>
			</div>
		</div>
	</div>
@endsection

@section('novatech_scripts')
<script>
$(document).ready(function() {
    emailIndex = $("#lastkey").val();
    $('#nova_insurance_email')
        // Add button click handler
        .on('click', '.addButton', function() {
            emailIndex++;
            var $template = $('#emailTemplate'),
                $clone    = $template
                                .clone()
                                .removeClass('hide')
                                .removeAttr('id')
                                .attr('data-email-index', emailIndex)
                                .insertBefore($template);

            // Update the name attributes
	            $clone
	                .find('[name="email"]').attr('name', 'iEmail[' + emailIndex + '][email]').end()
	                .find('[name="contact_name"]').attr('name', 'iEmail[' + emailIndex + '][contact_name]').end()
	                .find('[name="reason"]').attr('name', 'iEmail[' + emailIndex + '][reason]').end();
        })

        // Remove button click handler
        .on('click', '.removeButton', function() {
            var $row  = $(this).parents('.form-group'),
                index = $row.attr('data-email-index');
            // Remove element containing the fields
            $row.remove();
        });
});
</script>
@endsection