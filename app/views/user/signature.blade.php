@extends('layouts.master')


@section('meta_title')
    Signature de {{ $user->fullname }}
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-8">
            <h2>{{ $user->fullname }}</h2>
        </div>
        <div class="col-sm-4">
            <div class="title-action">
                <a href="?download=1" class="btn btn-primary">Télécharger ({{ceil(strlen($signature) / 1024)}}Kb)</a>
                <a href="{{ URL::route('user_modify', $user->id) }}" class="btn btn-default">Modifier</a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="ibox-content">
                    {{$signature}}
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="ibox-title">
                    Code HTML correspondant
                </div>
                <div class="ibox-content">
                    <pre>{{htmlentities($signature)}}</pre>
                </div>
            </div>
        </div>
    </div>

@stop

@section('javascript')

@stop
