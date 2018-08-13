@extends('emails.master')

@section('title')
    {{$_ENV['organisation_name']}} - Bienvenue à Etincelle Coworking !
@stop

@section('content')
    {{$content}}
@stop


