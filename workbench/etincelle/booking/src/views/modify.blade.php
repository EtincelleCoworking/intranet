@extends('layouts.master')

@section('meta_title')
    Modification d'une réservation
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Modification d'une réservation</h2>
        </div>

    </div>
@stop

@section('content')

    <?php


    $ressources = Ressource::whereIsBookable(true)
        ->where('locations.city_id', '=', Auth::user()->location->city_id)
        ->join('locations', 'ressources.location_id', '=', 'locations.id')
        //->with('location')
                ->select('ressources.*')
        ->orderBy('locations.name', 'ASC')
        ->get();

    $ressources_by_location = array();
    foreach ($ressources as $ressource) {
        if (!isset($ressources_by_location[$ressource->location_id]['location_name'])) {
            $ressources_by_location[$ressource->location_id]['location_name'] = $ressource->location->full_name;
        }
        if (!isset($ressources_by_location[$ressource->location_id]['ressources'])) {
            $ressources_by_location[$ressource->location_id]['ressources'] = array();
        }
        $ressources_by_location[$ressource->location_id]['ressources'][] = $ressource;

    }
    ?>


    <div class="row">
        <div class="">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5>Réservation</h5>
                </div>
                <div class="ibox-content">
                    {{ Form::model($booking_item, array('route' => array('booking_modify_check', $booking_item->id))) }}
                    <div class="col-md-6 col-xs-12">
                        @if (Auth::user()->isSuperAdmin())
                            <div>
                                {{ Form::label('title', 'Client') }}
                                <p>{{ Form::select('user_id', User::Select('Sélectionnez un client'), $booking_item->booking->user_id, array('id' => 'booking-user','class' => 'form-control')) }}</p>
                            </div>
                            <div>
                                {{ Form::label('title', 'Organisation') }}
                                <p>{{ Form::select('organisation_id', Organisation::Select('Sélectionnez une organisation'), $booking_item->booking->organisation_id, array('id' => 'booking-organisation','class' => 'form-control')) }}</p>
                            </div>
                        @endif

                        <div>
                            {{ Form::label('title', 'Titre') }}
                            <p>{{ Form::text('title', $booking_item->booking->title, array('class' => 'form-control')) }}</p>
                        </div>

                        <div>
                            {{ Form::label('description', 'Description') }}
                            <p>{{ Form::textarea('description', $booking_item->booking->content, array('class' => 'form-control')) }}</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-xs-12">
                        <div class="col-xs-4">
                            {{ Form::label('date', 'Date') }}
                            <p>{{ Form::text('date', date('d/m/Y', strtotime($booking_item->start_at)), array('class' => 'form-control datePicker')) }}</p>
                        </div>

                        <div class="col-xs-4">
                            {{ Form::label('start', 'Début') }}
                            <p>{{ Form::select('start', Booking::selectableHours(), date('H:i', strtotime($booking_item->start_at)), array('class' => 'form-control')) }}</p>
                        </div>

                        <div class="col-xs-4">
                            {{ Form::label('end', 'Fin') }}
                            <p>{{ Form::select('end', Booking::selectableHours(), date('H:i', strtotime($booking_item->start_at) + $booking_item->duration * 60), array('class' => 'form-control')) }}</p>
                        </div>
                        <div class="col-xs-12">
                            <label for="meeting-add-isprivate" style="font-weight: normal;">
                                <p>
                                    {{ Form::checkbox('is_private', true, $booking_item->booking->is_private, array('id'=> 'meeting-add-isprivate')) }}
                                    Événement privé
                                    <br/>
                                    <span class="text-muted">
                                        <small>Les événements privés ne sont visibles que par vous</small>
                                    </span>
                                </p>
                            </label>
                        </div>
                        <div class="col-xs-12">
                            <label for="meeting-add-registration" style="font-weight: normal;">
                                <p>
                                    {{ Form::checkbox('is_open_to_registration', true, $booking_item->is_open_to_registration, array('id'=> 'meeting-add-registration')) }}
                                    Permettre les inscriptions
                                    <br/>
                                    <span class="text-muted">
                                        <small>Les autres membres pourrons s'inscrire à cet événément.</small>
                                    </span>
                                </p>
                            </label>
                        </div>
                        @if(count($ressources)>1)
                            <div class="col-xs-12">
                                {{ Form::label('rooms', 'Lieu') }}
                                @foreach($ressources_by_location as $location_ressource)
                                    <p>{{$location_ressource['location_name']}}</p>
                                    @foreach($location_ressource['ressources'] as $ressource_)
                                        <p>
                                        <span class="label" style="{{$ressource_->labelCss}}">
                                    {{ Form::checkbox('rooms[]', $ressource_->id, $ressource_->id == $booking_item->ressource_id, array('id'=> sprintf('meeting-add-room%d', $ressource_->id))) }}
                                            &nbsp;
                                            <label for="meeting-add-room{{$ressource_->id}}"
                                                   style="font-weight: normal;">
                                                {{$ressource_->name}}
                                            </label>
                                        </span>
                                        </p>
                                    @endforeach
                                @endforeach
                            </div>
                        @else
                            @foreach($ressources as $ressource_)
                                {{ Form::hidden('rooms[]', $ressource_->id) }}
                            @endforeach
                        @endif

                        @if (Auth::user()->isSuperAdmin())
                            <div class="col-xs-12">
                                <div>
                                    {{ Form::label('invoice_id', 'Facture') }}
                                    <p>{{ Form::select('invoice_id', Invoice::SelectAll('Sélectionnez une facture', $booking_item->booking->user_id), $booking_item->invoice_id, array('id' => 'booking-invoice','class' => 'form-control')) }}</p>
                                </div>
                                <div>
                                    <p>
                                        {{ Form::checkbox('is_free', true, $booking_item->is_free, array('id'=> 'meeting-is-free')) }}
                                        {{ Form::label('meeting-is-free', 'Réservation offerte') }}

                                        </span>
                                    </p>
                                </div>
                            </div>
                        @endif


                    </div>

                    <div class="row">
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        {{ Form::submit('Enregistrer', array('class' => 'btn btn-success')) }}
                        <a href="{{ URL::route('booking_with_date', array('now'=> date('Y-m-d', strtotime($booking_item->start_at)))) }}"
                           class="btn btn-white">Annuler</a>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

@stop

@section('javascript')
    <script type="text/javascript">
        $().ready(function () {

            $('#booking-user').select2();
            $('#booking-organisation').select2();
            $('.datePicker').datepicker();
        });
    </script>
@stop
