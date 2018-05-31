@extends('layouts.master')

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    <p>
                        <a href="{{URL::route('planning')}}"
                           class="btn btn-primary btn-xs">Tous</a>

                        @foreach($staff as $member)
                            <a href="{{URL::route('planning_member', $member->id)}}"
                               class="btn btn-default btn-xs">{{$member->fullname}}</a>
                        @endforeach
                    </p>

                    <div id="calendar"></div>
                </div>
            </div>
        </div>

    </div>

    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-10">

        </div>

        <p>
            @foreach($locations as $location)
                <a href="#" class="btn btn-default btn-xs">{{$location->fullname}}</a>
            @endforeach
        </p>
        <p>
            @foreach($staff as $member)
                <a href="#" class="btn btn-xs">{{$member->fullname}}</a>
            @endforeach
        </p>
        <p>
            Team Planning
        <ul>
            <li>1 couleur par espace &gt; légende</li>
            <li>Vue semaine par semaine
                <ul>
                    <li>Par espace (mettre en fond les plages sur lesquelles on a des réservations, plages avec le nom
                        de la personne)
                    </li>
                    <li>Par personne (mettre le nom de l'espace / couleur)</li>
                </ul>
            </li>
        </ul>
        </p>

        <p>
            Vue liste
        <ul>
            <li>Ajouter (2 créneaux possibles, récurrence (tous les X jours, sélection des jours L-V par défaut,
                jusqu'au), raccourcis pour les heures type)
            </li>
            <li>Modifier</li>
            <li>Supprimer (multiple)</li>
        </ul>
        Filtre
        <ul>
            <li>Personne</li>
            <li>Espace</li>
            <li>Période du ... au ...</li>
        </ul>
        </p>

    </div>

@stop



@section('stylesheets')
    {{ HTML::style('css/plugins/fullcalendar/fullcalendar.min.css') }}
    {{ HTML::style('css/plugins/fullcalendar/fullcalendar.print.min.css', array('media'=> 'print')) }}

@stop

@section('javascript')

    {{ HTML::script('js/plugins/fullcalendar/fullcalendar.min.js') }}
    {{ HTML::script('js/locale/fr.js') }}

    <script type="text/javascript">


        $().ready(function () {
            $('#calendar').fullCalendar({
//                schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
                defaultView: 'agendaWeek', // agendaWeek
//                defaultDate: '',
                nowIndicator: true,
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: ''
                },
                editable: false,
                firstDay: 1,
                lang: 'fr',
                allDaySlot: false,
                selectable: true,
                selectHelper: true,
                minTime: '07:00',
                maxTime: '21:00',
                axisFormat: 'HH:mm',
                scrollTime: '08:30',
                eventTextColor: '#000000',
                slotDuration: '00:30:00',
                select: function (start, end) {
                    var startMoment = moment(start);
                    var endMoment = moment(end);

//                    window.location.href = '{{ URL::route('booking_new_full', array('start_at' => 999999, 'end_at' => 888888)) }}'
//                        .replace('999999', startMoment.format('YYYY-MM-DD HH:mm')).replace('888888', endMoment.format('YYYY-MM-DD HH:mm'));

                },
                eventClick: function (calEvent, jsEvent, view) {
                },
                eventSources: [
                    '{{  URL::route('planning_json_member') }}'
                ],
                weekNumbers: true,
                dayClick: function (date, jsEvent, view) {
                },
                droppable: false, // this allows things to be dropped onto the calendar
                drop: function () {
                },
                eventResize: function (event, delta, revertFunc) {

                    alert(event.title + " end is now " + event.end.format());

                    if (!confirm("is this okay?")) {
                        revertFunc();
                    }
                },
                eventDrop: function (event, delta, revertFunc) {

                }
            });

        });
    </script>

@stop





