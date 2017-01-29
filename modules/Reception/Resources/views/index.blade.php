<form action='/reception/newPolicy/initialDocumentation?api_token=2xpHVld5Kag9Kgp3qFbAySFoYLNc8QaEfRf6ZS54BbtmWru502RFNdzrfGJJ' method="post" enctype="multipart/form-data">
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
    <div class="form-group">
        {!! Form::label('email', 'Email') !!}
        {!! Form::text('email', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
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
        {!! Form::label('had_insurance', 'had_insurance') !!}
        {!! Form::text('had_insurance', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
    </div>
    <div class="form-group">
        {!! Form::label('plan_id', 'plan_id') !!}
        {!! Form::text('plan_id', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
    </div>
    <div class="form-group">
        {!! Form::label('upload_cheque', 'upload_cheque') !!}
        {!! Form::text('upload_cheque', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
    </div>
    <div class="form-group">
        {!! Form::label('agente_id', 'agente_id') !!}
        {!! Form::text('agente_id', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
    </div>
    </div>
    <div class="form-group">
        <input type="text" name="description_files[]">
        <input type="file" name="filefields[]">
    </div>
    <div class="form-group">
        <input type="text" name="description_files[]">
        <input type="file" name="filefields[]">
    </div>
    <input type="submit">
</form>