@extends('layouts.master')

@section('meta_title')
    Présence de {{$user->fullname}}
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>Présence de {{$user->fullname}}</h2>
        </div>

    </div>
@stop

@section('content')


    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibon-title">
                    {{ Form::open(array('route' => array('stats_devices'))) }}
                    {{ Form::select('user_id', User::Select(null), $user->id, array('id' => 'filter-client','class' => 'form-control')) }}
                    {{ Form::close() }}
                </div>
                <div class="ibox-content">
                    <p>En vert la période d'abonnement, en orange les jours où un des périphériques de l'utilisateur a
                        été détecté (disponible uniquement depuis 10/2017)</p>
                    @include('stats._daily_data', array('days'=>$days, 'days2'=>$days2))
                </div>
            </div>

        </div>
    </div>


@stop



@section('javascript')
    <script type="text/javascript">
        $().ready(function () {
            $('#filter-client').select2({placeholder: 'Sélectionnez un client', allowClear: true});
        });
    </script>
@stop




