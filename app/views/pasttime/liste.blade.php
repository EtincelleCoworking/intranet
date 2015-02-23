@extends('layouts.master')

@section('meta_title')
    Liste des temps passés
@stop

@section('content')
    <div class="pull-right">
        {{ Form::open(array('route' => array('pasttime_list'))) }}
            <span class="btn btn-info">
                <div class="col-md-2 input-group-sm"><input type="text" name="filtre_month" value="{{ ((Session::get('filtre_pasttime.month'))?:date('m')) }}" class="monthDropper form-control"></div>
                <div class="col-md-2 input-group-sm"><input type="text" name="filtre_year" value="{{ ((Session::get('filtre_pasttime.year'))?:date('Y')) }}" class="yearDropper form-control"></div>
                <div class="col-md-2">{{ Form::submit('Filtrer', array('class' => 'btn btn-sm btn-default')) }}</div>
                <div class="col-md-2"><a href="{{ URL::route('pasttime_add') }}" class="btn btn-sm btn-success">Ajouter</a></div>
            </span>
        {{ Form::close() }}
    </div>

    <h1>Liste des temps passés</h1>
    @if(count($times)==0)
        <p>Aucune donnée.</p>
    @else
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    @if (Auth::user()->role == 'superadmin')<th>Utilisateur</th>@endif
                    <th>Ressource</th>
                    <th>Arrivé</th>
                    <th>Départ</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($times as $time)
                <tr>
                    <td>{{ date('d/m/Y', strtotime($time->date_past)) }}</td>
                    @if (Auth::user()->role == 'superadmin')<td>{{ $time->user->fullname }}</td>@endif
                    <td>{{ $time->ressource->name }}</td>
                    <td>{{ date('H:i', strtotime($time->time_start)) }}</td>
                    <td>{{ date('H:i', strtotime($time->time_end)) }}</td>
                    <td>
                        {{ $time->past_time }}
                        @if ($time->comment)
                            <span data-toggle="tooltip" data-placement="left" title="{{ $time->comment }}"><i class="fa fa-question-circle"></i></span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ URL::route('pasttime_modify', $time->id) }}" class="btn btn-xs btn-success">Modifier</a>
                        @if (Auth::user()->role == 'superadmin')<a href="{{ URL::route('pasttime_delete', $time->id) }}" class="btn btn-xs btn-danger" data-method="delete" data-confirm="Etes-vous certain de vouloir supprimer cette ligne ?" rel="nofollow">Supprimer</a>@endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $times->links() }}
    @endif
@stop


@section('javascript')
<script type="text/javascript">
$().ready(function(){
    $('.yearDropper').dateDropper({animate_current: false, format: "Y", placeholder: "{{ ((Session::get('filtre_pasttime.year'))?:date('Y')) }}"});
    $('.monthDropper').dateDropper({animate_current: false, format: "m", placeholder: "{{ ((Session::get('filtre_pasttime.month'))?:date('m')) }}"});
});
</script>
@stop