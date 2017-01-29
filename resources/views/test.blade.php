@extends('layouts/master')

@section('novatech_module_title')
    Dashboard
@stop

@section('novatech_content')
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-content">
                <button class="btn btn-primary dim btn-large-dim" type="button">
                    <a href="{{ action('\Modules\User\Http\Controllers\UserController@index') }}">Recepcion</a>
                </button>
                <button class="btn btn-primary dim btn-large-dim" type="button">
                    <a href="{{ action('\Modules\User\Http\Controllers\UserController@index') }}">Emision</a>
                </button>
                <button class="btn btn-primary dim btn-large-dim" type="button">
                    <a href="{{ action('\Modules\User\Http\Controllers\UserController@index') }}">Administraciòn</a>
                </button>
                <button class="btn btn-warning dim btn-large-dim" type="button"><i class="fa fa-warning"></i></button>
                <button class="btn btn-danger  dim btn-large-dim" type="button"><i class="fa fa-heart"></i></button>
            </div>
        </div>
    </div>
@stop