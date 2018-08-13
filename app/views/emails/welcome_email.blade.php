@extends('emails.master')

@section('title')
    Bienvenue à {{$_ENV['organisation_name']}} !
@stop

@section('content')
    {{$content}}
@stop


