@extends('layouts.master')

@section('meta_title')
    Anniversaires
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>Anniversaires</h2>
        </div>
    </div>
@stop

@section('content')
    @foreach ($months as $month)
        <h2>{{ $month }} - {{ date('F', strtotime(date('Y-'.$month.'-d'))) }}</h2>
        @include('user.birthday_content', ['users' => $users[$month]])
    @endforeach

    <h2>Date d'anniversaire inconnue</h2>
    @include('user.birthday_content', ['users' => $others])
@stop

@section('javascript')

    <script type="text/javascript">
        $().ready(function () {

        });
    </script>
@stop