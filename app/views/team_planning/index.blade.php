@extends('layouts.master')

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    <div>
                        <p>
                            @if(!$location_id && !$user_id)
                                <a href="{{URL::route('planning')}}"
                                   class="btn btn-primary btn-xs">Tous</a>
                            @else
                                <a href="{{URL::route('planning')}}"
                                   class="btn btn-default btn-xs">Tous</a>
                            @endif

                            @foreach($staff as $member)
                                @if($user_id == $member->id)
                                    <a href="{{URL::route('planning')}}?user_id={{$member->id}}"
                                       class="btn btn-primary btn-xs">{{$member->fullname}}</a>
                                @else
                                    <a href="{{URL::route('planning')}}?user_id={{$member->id}}"
                                       class="btn btn-default btn-xs">{{$member->fullname}}</a>
                                @endif
                            @endforeach
                        </p>
                    </div>
                    <div id="calendar"></div>
                </div>
            </div>
        </div>

    </div>

    <div class="row wrapper border-bottom white-bg page-heading">
        <p>
            Team Planning
        <ul>
            <li>Vue semaine par semaine
                <ul>
                    <li>Par espace (mettre en fond les plages sur lesquelles on a des réservations, plages avec le nom
                        de la personne)
                    </li>
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
    {{ HTML::style('css/scheduler.min.css') }}

    <style type="text/css">
        .fc-bgevent {
            background-color: #ff0000;
        }
    </style>
@stop

@section('javascript')

    {{ HTML::script('js/plugins/fullcalendar/fullcalendar.min.js') }}
    {{ HTML::script('js/scheduler.min.js') }}
    {{ HTML::script('js/locale/fr.js') }}

    <script type="text/javascript">


        $().ready(function () {
            $('#calendar').fullCalendar({
                schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
                defaultView: 'agendaWeek', // agendaWeek
                nowIndicator: true,
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'agendaWeek,agendaDay'
                },
                editable: false,
                firstDay: 1,
                lang: 'fr',
                allDaySlot: false,
                selectable: true,
                selectHelper: true,
                contentHeight: 700,
                minTime: '07:00',
//                maxTime: '22:00',
                axisFormat: 'HH:mm',
                scrollTime: '08:30',
                eventTextColor: '#000000',
                slotDuration: '00:30:00',
                //groupByResource: true,
                //groupByDateAndResource: true,
                /*
                                businessHours: {
                                    // days of week. an array of zero-based day of week integers (0=Sunday)
                                    dow: [1, 2, 3, 4, 5],
                                    start: '08:00',
                                    end: '19:00'
                                },
                */
                resources: [
                        @foreach($locations as $location)
                    {
                        id: '{{$location->id}}',
                        title: '{{$location->name}}'
                    },
                    @endforeach
                ],
                select: function (start, end) {
                    var startMoment = moment(start);
                    var endMoment = moment(end);

//                    window.location.href = '{{ URL::route('booking_new_full', array('start_at' => 999999, 'end_at' => 888888)) }}'
//                        .replace('999999', startMoment.format('YYYY-MM-DD HH:mm')).replace('888888', endMoment.format('YYYY-MM-DD HH:mm'));

                },
                eventClick: function (calEvent, jsEvent, view) {
                },
                eventSources: [
                    '{{ $json_url }}'
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





