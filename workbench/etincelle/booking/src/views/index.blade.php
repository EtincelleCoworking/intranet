@extends('layouts.master')

@section('meta_title')
    Réservations
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-8">
            <h2>Réservations</h2>

            Légende:
            @foreach(Ressource::whereIsBookable(true)->get() as $ressource)

                <div class="label" style="background-color: {{$ressource->booking_background_color}}; color: {{ adjustBrightness($ressource->booking_background_color, -128)}}; border: 1px solid {{ adjustBrightness($ressource->booking_background_color, -32)}}; margin-right: 10px; opacity: 0.75">
                    {{$ressource->name}}
                </div>
            @endforeach

        </div>
        <div class="col-sm-4">
            <div class="title-action">
                <a href="#" class="btn btn-primary" id="meeting-add">Nouvelle réservation</a>
            </div>
        </div>

    </div>
@stop

@section('content')
    <div class="modal inmodal fade" id="newBookingDialog" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                {{ Form::open(array('route' => 'booking_create', 'class' => 'form', 'id'=>'meeting-form'), array()) }}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                        <span class="sr-only">Close</span>
                    </button>
                    <h4 class="modal-title">Nouvelle réservation</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        {{ Form::hidden('booking_id') }}
                        {{ Form::hidden('id') }}
                        @if (Auth::user()->isSuperAdmin())
                            <div class="col-xs-12">
                                {{ Form::label('title', 'Client') }}
                                {{ Form::select('user_id', User::Select('Sélectionnez un client'), null, array('id' => 'booking-user','class' => 'form-control')) }}
                            </div>
                        @endif

                        <div class="col-xs-12">
                            {{ Form::label('title', 'Titre') }}
                            <p>{{ Form::text('title', null, array('class' => 'form-control')) }}</p>
                        </div>

                        <div class="col-xs-4">
                            {{ Form::label('date', 'Date') }}
                            <p>{{ Form::text('date', null, array('class' => 'form-control datePicker')) }}</p>
                        </div>

                        <div class="col-xs-4">
                            {{ Form::label('start', 'Début') }}
                            <p>{{ Form::select('start', Booking::selectableHours(), false, array('class' => 'form-control')) }}</p>
                        </div>

                        <div class="col-xs-4">
                            {{ Form::label('end', 'Fin') }}
                            <p>{{ Form::select('end', Booking::selectableHours(), false, array('class' => 'form-control')) }}</p>
                        </div>

                        <div class="col-xs-12">
                            {{ Form::label('rooms', 'Lieu') }}
                            @foreach(Ressource::bookable() as $id => $ressource)
                                <p>
                                    {{ Form::checkbox('rooms[]', $id, false, array('id'=> sprintf('meeting-add-room%d', $id))) }}
                                    <label for="meeting-add-room{{$id}}">{{$ressource}}</label>
                                </p>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="meeting-submit">Sauvegarder</button>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>

    <div class="modal inmodal fade" id="BookingDialog" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    {{--<a href="#" class="btn btn-default pull-left col-xs-1" id="meeting-unlocker">--}}
                        {{--<span class="fa fa-lock"></span>--}}
                    {{--</a>--}}

                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                        <span class="sr-only">Close</span>
                    </button>
                    <h4 class="modal-title">
                        <!-- booking title -->
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-4">
                            Date
                        </div>
                        <div class="col-xs-8" id="meeting-view-date">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-4">
                            Heure
                        </div>
                        <div class="col-xs-8" id="meeting-view-hours">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-4">
                            Lieu
                        </div>
                        <div class="col-xs-8" id="meeting-view-location">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <a href="#" class="btn btn-danger pull-left" id="meeting-delete">Supprimer</a>

                    <button type="button" class="btn btn-primary" data-dismiss="modal">Fermer</button>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>




    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>

    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    Exporter au format iCalendar
                </div>
                <div class="ibox-content">
                    <p>Vous pouvez exporter toutes vos réservations privées via cette adresse:</p>
                    <pre>{{route('booking_ical', Auth::user()->booking_key)}}</pre>

                    <p>Vous pouvez exporter tous les rendez-vous publiques via cette adresse:</p>
                    <pre>{{route('booking_ical', 'public')}}</pre>

                    <p>Vous pouvez exporter toutes les réservations via cette adresse:</p>
                    <pre>{{route('booking_ical', Auth::user()->booking_key.'_all')}}</pre>

                </div>
            </div>
        </div>

    </div>
@stop





@section('stylesheets')
    {{ HTML::style('css/plugins/fullcalendar/fullcalendar.css') }}
    {{ HTML::style('css/plugins/fullcalendar/fullcalendar.print.css', array('media'=> 'print')) }}

    <style type="text/css">
        @foreach(Ressource::whereIsBookable(true)->get() as $ressource)

.fc-event.booking-ofuscated-{{$ressource->id}} {
            background: repeating-linear-gradient(
            135deg,
                    {{ adjustBrightness($ressource->booking_background_color, -32)}},
                    {{adjustBrightness($ressource->booking_background_color, -32)}} 10px,
                    {{$ressource->booking_background_color}} 10px,
                    {{$ressource->booking_background_color}} 20px
            );
        }

        .fc-event.booking-ofuscated-{{$ressource->id}}.booking-completed {
            background: repeating-linear-gradient(
            135deg,
                    {{ hexColorToRgbWithTransparency(adjustBrightness($ressource->booking_background_color, -32), '0.4')}},
                    {{hexColorToRgbWithTransparency(adjustBrightness($ressource->booking_background_color, -32), '0.4')}} 10px,
                    {{hexColorToRgbWithTransparency($ressource->booking_background_color, '0.4')}} 10px,
                    {{hexColorToRgbWithTransparency($ressource->booking_background_color, '0.4')}} 20px
            );
        }
        @endforeach
    </style>

@stop

@section('javascript')

    {{ HTML::script('js/plugins/fullcalendar/fullcalendar.min.js') }}
    {{ HTML::script('js/plugins/fullcalendar/lang-all.js') }}

    <script type="text/javascript">
        function hideDeleteButton() {
            $('#meeting-form input[name=id]').val(false);
            $('#meeting-form input[name=booking_id]').val(false);
            $('#meeting-delete').hide();

        }

        $().ready(function () {
            $('#meeting-unlocker').on({
                mouseenter: function () {
                    $(this).find('span').attr('class', 'fa fa-unlock');
                },
                mouseleave: function () {
                    $(this).find('span').attr('class', 'fa fa-lock');
                }
            });

            $('#meeting-add')
                    .click(function () {
                        var start = moment().add(7, 'days');
                        var $form = $('#meeting-form');
                        $form.find('input[name="title"]').val('{{Auth::user()->fullname}}');
                        $form.find('input[name="date"]').val(start.format('DD/MM/YYYY'));
                        $form.find('select[name="start"]').val(start.format('HH:00'));
                        $form.find('select[name="end"]').val(moment().add({{ Config::get('booking::default_meeting_duration', 1) }}, 'hours').format('HH:00'));
                        $form.find('input[name="rooms[]"]').attr('checked', false);

                        $('#newBookingDialog').modal('show');

                        return false;
                    });


            $('#meeting-delete')
                    .hide()
                    .click(function () {
                        $.ajax({
                            dataType: 'json',
                            url: '{{ URL::route('booking_delete_ajax') }}',
                            type: "POST",
                            data: {
                                booking_id: $('#meeting-form input[name=booking_id]').val(),
                                id: $('#meeting-form input[name=id]').val()
                            },
                            success: function (data) {
                                if (data.status == 'KO') {
                                    alert(data.message);
                                } else {
                                    $('#calendar').fullCalendar('removeEvents', data.id);
                                    $('#BookingDialog').modal('hide');
                                }
                            },
                            error: function (data) {
                                // afficher un message générique?
                                $('#BookingDialog').modal('hide');
                            }
                        });
                        return false;
                    });

            $('#meeting-submit').click(function () {
                $.ajax({
                    dataType: 'json',
                    url: '{{ URL::route('booking_create') }}',
                    type: "POST",
                    data: $('#meeting-form').serialize(),
                    success: function (data) {
                        if (data.status == 'KO') {
                            for (field in data.messages) {
                                alert(data.messages[field]); // field + ': ' +
                            }
                        } else {
                            for (var i = 0; i < data.events.length; i++) {
                                $('#calendar').fullCalendar('renderEvent', data.events[i], true);
                            }
                            $('#newBookingDialog').modal('hide');
                        }
                    },
                    error: function (data) {
                        $('#newBookingDialog').modal('hide');
                    }
                });

                return false;
            });
            /* initialize the calendar
             -----------------------------------------------------------------*/
            var date = new Date();
            var d = date.getDate();
            var m = date.getMonth();
            var y = date.getFullYear();

            $('#calendar').fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'agendaWeek,agendaDay' // month,basicWeek,basicDay,
                },
                eventRender: function(event, element) {
                    if(event.is_private){
                        element.find(".fc-time").before($("<span class=\"fa fa-lock pull-right\"></span>"));
                    }else{
                        //element.find(".fc-time").before($("<span class=\"fa fa-unlock pull-right\"></span>"));
                    }
                },
                editable: false,
                firstDay: 1,
                lang: 'fr',
                defaultView: 'agendaWeek',
                allDaySlot: false,
                selectable: true,
                selectHelper: true,
                minTime: '07:00',
//                maxTime: '22:00',
                axisFormat: 'HH:mm',
                scrollTime: '08:30',
                eventTextColor: '#000000',
                slotDuration: '00:30:00',
                select: function (start, end) {
                    var $form = $('#meeting-form');
                    $form.find('input[name="title"]').val('{{Auth::user()->fullname}}');
                    $form.find('input[name="date"]').val(start.format('DD/MM/YYYY'));
                    $form.find('select[name="start"]').val(start.format('HH:mm'));
                    $form.find('select[name="end"]').val(end.format('HH:mm'));
                    $form.find('input[name="rooms[]"]').attr('checked', false);

                    $('#newBookingDialog').modal('show');
                    $('#calendar').fullCalendar('unselect');

                },
                eventClick: function (calEvent, jsEvent, view) {

//                    alert('Event: ' + calEvent.title + ' ' + calEvent.booking_id + ' ' + calEvent.booking_item_id);
//                    alert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);
//                    alert('View: ' + view.name);

                    // change the border color just for fun
//                    $(this).css('border-color', 'red');

                    $('#meeting-form input[name=id]').val(calEvent.id);
                    $('#meeting-form input[name=booking_id]').val(calEvent.booking_id);

                    var $dialog = $('#BookingDialog');
                    $dialog.find('.modal-header .modal-title').html(calEvent.title);
                    $dialog.find('#meeting-view-date').html(calEvent.start.format('DD/MM/YYYY'));
                    $dialog.find('#meeting-view-hours').html(
                            calEvent.start.format('HH:mm')
                            + ' - '
                            + calEvent.end.format('HH:mm')
                    );
                    $dialog.find('#meeting-view-location').html(calEvent.location);

                    if (calEvent.canDelete) {
                        $('#meeting-delete').show();
                    } else {
                        $('#meeting-delete').hide();
                    }

                    $dialog.modal('show');


                },
                eventSources: [
                    '{{  URL::route('booking_list_ajax') }}'
                ],
//                businessHours: {
//                    start: '09:00', // a start time (10am in this example)
//                    end: '18:00', // an end time (6pm in this example)
//
//                    dow: [1, 2, 3, 4, 5]
//                    // days of week. an array of zero-based day of week integers (0=Sunday)
//                    // (Monday-Thursday in this example)
//                },
                weekNumbers: true,
                dayClick: function (date, jsEvent, view) {

//                    alert('Clicked on: ' + date.format());
//
//                    alert('Coordinates: ' + jsEvent.pageX + ',' + jsEvent.pageY);
//
//                    alert('Current view: ' + view.name);
//
//                    // change the day's background color just for fun
//                    $(this).css('background-color', 'red');
//
                    //$('booking-date').val(date.format('dd/mm/YYYY'));

                },
                droppable: false, // this allows things to be dropped onto the calendar
                drop: function () {
                    // is the "remove after drop" checkbox checked?
//                    if ($('#drop-remove').is(':checked')) {
//                        // if so, remove the element from the "Draggable Events" list
//                        $(this).remove();
//                    }
                },
                eventResize: function (event, delta, revertFunc) {

//                    alert(event.title + " end is now " + event.end.format());
//
//                    if (!confirm("is this okay?")) {
//                        revertFunc();
//                    }
//

                    $.ajax({
                        dataType: 'json',
                        url: '{{ URL::route('booking_ajax_update') }}',
                        type: "POST",
                        data: {
                            id: event.id,
                            start: event.start.format(),
                            end: event.end.format()
                        },
                        success: function (data) {
                            if (data.status == 'KO') {
                                alert(data.message);
                                revertFunc();
                            } else {
                                // ok
                            }
                        },
                        error: function (data) {
                            // afficher un message générique?
                            revertFunc();
                        }
                    });
                },
                eventDrop: function (event, delta, revertFunc) {
                    $.ajax({
                        dataType: 'json',
                        url: '{{ URL::route('booking_ajax_update') }}',
                        type: "POST",
                        data: {
                            id: event.id,
                            start: event.start.format(),
                            end: event.end.format()
                        },
                        success: function (data) {
                            if (data.status == 'KO') {
                                alert(data.message);
                                revertFunc();
                            } else {
                                // ok
                            }
                        },
                        error: function (data) {
                            // afficher un message générique?
                            revertFunc();
                        }
                    });
                }
            });


            $('#newBookingDialog').on('shown.bs.modal', function () {
                $('#booking-user').select2();
                $('.datePicker').datepicker();
            });

            $('#booking-user').on("select2:select", function (e) {
                $('#newBookingDialog').find('input[name="title"]').val($('#booking-user option:selected').text());
            });


            $.fn.modal.Constructor.prototype.enforceFocus = $.noop;
        });
    </script>

@stop



