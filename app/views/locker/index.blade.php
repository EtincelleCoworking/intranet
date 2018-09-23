@extends('layouts.master')

@section('meta_title')
    Gestion des casiers
@stop


@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>
                Gestion des casiers
            </h2>
        </div>

    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            @if(!count($lockers))
                <div class="ibox">

                    <div class="ibox-title">
                        <h3>D'après les informations en notre possession, vous n'utilisez aucun casier.</h3>
                    </div>
                    <div class="ibox-content">
                        <p>Pour utiliser un casier, rendez-vous devant les armoires et cherchez un casier ayant une clef
                            sur la porte. </p>
                        <p>A l'intérieur, vous trouverez les instructions à suivre pour utiliser ce casier.</p>
                        <p>En cas de besoin, nous nous tenons à votre disposition par email via <a
                                    href="mailto:support@etincelle-coworking.com">support@etincelle-coworking.com</a>.
                        </p>
                    </div>
                </div>

                <div class="ibox">

                    <div class="ibox-title">
                        <h3>Confirmer l'utilisation d'un casier</h3>
                    </div>
                    <div class="ibox-content">

                        <form action="{{URL::route('locker_take')}}" method="post">
                            <div class="row">
                                <div class="col-md-6">
                                    {{ Form::label('locker', 'Casier') }}
                                    <p>{{ Form::select('locker_id', Locker::available(), null, array('class' => 'form-control')) }}</p>
                                </div>
                                <div class="col-md-6">
                                    {{ Form::label('code', 'Code') }}
                                    <p>{{ Form::text('code', null, array('class' => 'form-control')) }}</p>
                                </div>
                            </div>
                            <input type="submit" value="Enregistrer" class="btn btn-primary"/>
                        </form>
                    </div>
                </div>
            @else
                @foreach($lockers as $locker)
                    <div class="ibox">

                        <div class="ibox-title">
                            <h3>Casier {{$locker->name}} ({{$locker->cabinet->name}})</h3>
                        </div>
                        <div class="ibox-content">
                            <p>Vous utilisez ce casier depuis
                                le {{date('d/m/Y à H:i', strtotime($locker->current_usage->taken_at))}}.</p>
                            <a href="{{URL::route('locker_release', $locker->id)}}" class="btn btn-danger">Libérer ce
                                casier</a>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>


@stop

@section('javascript')
    <script type="text/javascript">
        $().ready(function () {


        });
    </script>
@stop