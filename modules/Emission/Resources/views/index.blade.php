<form action='/emission/newPolicy/registerPayment/90?api_token=2xpHVld5Kag9Kgp3qFbAySFoYLNc8QaEfRf6ZS54BbtmWru502RFNdzrfGJJ' method="post" enctype="multipart/form-data">
    <div class="form-group">
        {!! Form::label('payment_method_id', 'method') !!}
        {!! Form::text('payment_method_id', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
    </div>
    <div class="form-group">
        {!! Form::label('date', 'Date') !!}
        {!! Form::text('date', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
    </div>
    <div class="form-group">
        {!! Form::label('amount', 'Cédula Identidad') !!}
        {!! Form::text('amount', null, ['class' => 'form-control',$disabled=>$disabled]) !!}
    </div>
    <div class="form-group">
        <input type="file" name="filefield">
    </div>
    <input type="submit">
</form>