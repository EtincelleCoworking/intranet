@extends('layouts.master')

@section('meta_title')
    Réservations
@stop

<?php $ressources = Ressource::whereIsBookable(true)
        ->where('location_id', '=', Auth::user()->default_location_id)
        ->get(); ?>

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-10">
            <h2>Réservations</h2>
            @if(count($ressources)>1)
                <form id="ressource_filter" action="#" autocomplete="off">
                    Légende:
                    @foreach($ressources as $ressource)
                        <div class="label" style="{{$ressource->labelCss}}">
                            <input type="checkbox" name="filter_ressource_{{$ressource->id}}"
                                   id="filter_ressource_{{$ressource->id}}" value="{{$ressource->id}}"
                                   checked="checked"/>
                            <label for="filter_ressource_{{$ressource->id}}"
                                   style="font-weight: 600;">{{$ressource->name}}</label>
                        </div>
                    @endforeach
                </form>
            @endif
        </div>
        <div class="col-sm-2">
            @if(count($ressources)>0)
            <div class="title-action">
                <a href="#" class="btn btn-primary" id="meeting-add">Nouvelle réservation</a>
            </div>
            @endif
        </div>

    </div>
@stop

@section('content')
    <div class="modal inmodal fade" id="newBookingDialog" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                {{ Form::open(array('route' => 'booking_create', 'class' => 'form', 'id'=>'meeting-form'), array()) }}
                {{ Form::hidden('id') }}
                {{ Form::hidden('booking_id') }}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                        <span class="sr-only">Close</span>
                    </button>
                    <h4 class="modal-title">Nouvelle réservation</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-6">
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

                            <div class="col-xs-12">
                                {{ Form::label('description', 'Description') }}
                                <p>{{ Form::textarea('description', null, array('class' => 'form-control')) }}</p>
                            </div>
                        </div>
                        <div class="col-xs-6">
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
                                <label for="meeting-add-isprivate" style="font-weight: normal;">
                                    <p>
                                        {{ Form::checkbox('is_private', true, count($ressources)>1, array('id'=> 'meeting-add-isprivate')) }}
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
                                        {{ Form::checkbox('is_open_to_registration', true, false, array('id'=> 'meeting-add-registration')) }}
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
                                    @foreach($ressources as $ressource)
                                        <p>
                                        <span class="label" style="{{$ressource->labelCss}}">
                                    {{ Form::checkbox('rooms[]', $ressource->id, false, array('id'=> sprintf('meeting-add-room%d', $ressource->id))) }}
                                            &nbsp;
                                            <label for="meeting-add-room{{$ressource->id}}"
                                                   style="font-weight: normal;">
                                                {{$ressource->name}}
                                            </label>
                                        </span>
                                        </p>
                                    @endforeach
                                </div>
                            @else
                                @foreach($ressources as $ressource)
                                    {{ Form::hidden('rooms[]', $ressource->id) }}
                                @endforeach
                            @endif


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
                    <div class="pull-left">
                        <span class="fa fa-3x fa-lock" id="meeting-unlocker"></span>
                    </div>

                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                        <span class="sr-only">Close</span>
                    </button>
                    <h4 class="modal-title">
                        <!-- booking title -->
                    </h4>

                    <div>
                        <i class="fa fa-calendar"></i>
                        <span id="meeting-view-date" style="margin-right: 20px"></span>
                        <i class="fa fa-clock-o"></i>
                        <span id="meeting-view-hours"></span>
                        <br/>
                        <i class="fa fa-tag"></i>
                        <span class="" id="meeting-view-location"></span>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row" id="meeting-view-description">
                        <div id="meeting-view-description-content">

                        </div>
                    </div>

                    <div class="row" id="meeting-view-members">

                        <div class="well well-sm">
                            <div class="pull-right">
                                <a href="#" class="btn btn-success" id="meeting-view-members-register">Inscription</a>
                                <a href="#" class="btn btn-success"
                                   id="meeting-view-members-unregister">Désincription</a>
                            </div>
                            <h4><span class="badge"></span> Participants</h4>

                            <div class="row">
                                <div class="col-lg-12" id="meeting-view-members-list">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <a href="#" class="btn btn-danger btn-outline pull-left" id="meeting-delete">Supprimer</a>
                    <a href="#" class="btn btn-default btn-outline" id="meeting-log-time">Comptabiliser</a>
                    <a href="#" class="btn btn-default btn-outline" id="meeting-modify">Modifier</a>
                    <a href="#" class="btn btn-default btn-outline" id="meeting-duplicate">Dupliquer</a>

                    <button type="button" class="btn btn-primary" data-dismiss="modal">Fermer</button>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>


    @if(count($ressources)>0)

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
    @else
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-content">
                        <p>Aucune ressource à louer sur cet espace.</p>
                    </div>
                </div>
            </div>

        </div>
    @endif
@stop





@section('stylesheets')
    {{ HTML::style('css/plugins/fullcalendar/fullcalendar.css') }}
    {{ HTML::style('css/plugins/fullcalendar/fullcalendar.print.css', array('media'=> 'print')) }}

    <style type="text/css">
        @foreach($ressources as $ressource)

.fc-event.booking-ofuscated-{{$ressource->id}}                            {
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
        formatMember = function (member) {
            return '<span id="meeting-view-members-member-' + member.id + '" class="pull-left" style="margin-right: 10px">'
                    + '<a href="' + member.profile_url + '">'
                    + '<img alt="' + member.fullname + '" title="' + member.fullname + '" class="img-circle img-responsive" src="' + member.avatar_url + '"/>'
                    + '</a></span> ';

        };
        var Etincelle = {
            Event: function () {
                this.event = null;
                this.id = null;
                this.booking_id = null;
                this.user_id = '{{Auth::id()}}';
                this.title = '{{str_replace("'", "\\'", Auth::user()->fullname)}}';
                this.description = '';
                this.start = moment().add(7, 'days');
                this.end = moment().add(7, 'days').add({{ Config::get('booking::default_meeting_duration', 1) }}, 'hours');
                this.ressource_id = null;
                this.is_private = true;
                this.is_accounted = false;
                this.is_open_to_registration = false;
            }
        };

        Etincelle.Event.prototype.populate = function (e) {
            this.event = e;
            this.id = e.id;
            this.booking_id = e.booking_id;
            this.editable = e.editable;
            this.canDelete = e.canDelete;
            this.user_id = e.user_id;
            this.title = e.title;
            this.description = e.description;
            this.start = e.start;
            this.end = e.end;
            this.ressource_id = e.ressource_id;
            this.is_private = e.is_private;
            this.is_accounted = e.is_accounted;
            this.is_open_to_registration = e.is_open_to_registration;
        };

        Etincelle.Event.prototype.edit = function () {
            var self = this;
            var $form = $('#meeting-form');
            $form.find('input[name="id"]').val(this.id);
            $form.find('input[name="booking_id"]').val(this.booking_id);
            $form.find('input[name="title"]').val(this.title);
            @if (Auth::user()->isSuperAdmin())
                $('select[name="user_id"]').select2().select2('val', this.user_id);
            @endif
            $form.find('textarea[name="description"]').val(this.description);
            $form.find('input[name="date"]').val(this.start.format('DD/MM/YYYY'));
            $form.find('select[name="start"]').val(this.start.format('HH:mm'));
            $form.find('select[name="end"]').val(this.end.format('HH:mm'));
            $form.find('input[name="rooms[]"]').each(function () {
                $(this).prop('checked', $(this).val() == self.ressource_id);
            });
            $form.find('input[name="is_private"]').prop('checked', this.is_private);
            $form.find('input[name="is_open_to_registration"]').prop('checked', this.is_open_to_registration);

            $('#newBookingDialog').modal('show');
        };
        Etincelle.Event.prototype.getLocation = function () {
            @foreach($ressources as $ressource)
            if ({{$ressource->id}} == this.ressource_id
            )
            {
                return '{{$ressource->name}}';
            }
            @endforeach
            return false;
        };

        Etincelle.Event.prototype.show = function () {
            var $dialog = $('#BookingDialog');
            if (this.is_private) {
                $dialog.find('#meeting-unlocker').prop('class', 'fa fa-2x fa-lock');
            } else {
                $dialog.find('#meeting-unlocker').prop('class', 'fa fa-2x fa-unlock');
            }
            if (this.is_open_to_registration) {
                $.ajax({
                    dataType: 'json',
                    url: '{{ route('api_booking_members', 999999) }}'.replace('999999', activeEvent.id),
                    type: "GET",
                    success: function (data) {
                        $('#meeting-view-members span').html(data.members.length);
                        var content = '';
                        for (var i = 0; i < data.members.length; i++) {
                            content += formatMember(data.members[i]);
                        }
                        $('#meeting-view-members-list').html(content);
                        if (data.is_member) {
                            $('#meeting-view-members-register').hide();
                            $('#meeting-view-members-unregister').show();
                        } else {
                            $('#meeting-view-members-register').show();
                            $('#meeting-view-members-unregister').hide();
                        }
                        $('#meeting-view-members').show();
                    },
                    error: function (data) {
                        // TODO
                    }
                });
            } else {
                $('#meeting-view-members').hide();
            }

            $dialog.find('.modal-header .modal-title').html(this.title);
            $dialog.find('#meeting-view-date').html(this.start.format('DD/MM/YYYY'));
            $dialog.find('#meeting-view-hours').html(
                    this.start.format('HH:mm')
                    + ' - '
                    + this.end.format('HH:mm')
            );
            $dialog.find('#meeting-view-location').html(this.getLocation());
            if (this.description != '') {
                $('#meeting-view-description-content').html(markdown.toHTML(this.description));
                $('#meeting-view-description').show();
            } else {
                $('#meeting-view-description').hide();
            }

            if (this.canDelete) {
                $('#meeting-delete').show();
            } else {
                $('#meeting-delete').hide();
            }

            if (this.editable) {
                $('#meeting-modify').show();
            } else {
                $('#meeting-modify').hide();
            }

            @if (Auth::user()->isSuperAdmin())
                $('#meeting-log-time').show();
            @else
                $('#meeting-log-time').hide();
            @endif

            $dialog.modal('show');
        }


        var activeEvent;

        $().ready(function () {
            $('#meeting-add')
                    .click(function () {
                        var event = new Etincelle.Event();
                        event.edit();
                        return false;
                    });

            $('#meeting-modify')
                    .click(function () {
                        $('#BookingDialog').modal('hide');
                        activeEvent.edit();
                        return false;
                    });

            $('#meeting-duplicate')
                    .click(function () {
                        $('#BookingDialog').modal('hide');
                        var newEvent = new Etincelle.Event();
                        newEvent.populate(activeEvent);
                        newEvent.id = null;
                        newEvent.booking_id = null;
                        newEvent.start.add(7, 'days');
                        newEvent.end.add(7, 'days');
                        activeEvent = newEvent;
                        activeEvent.edit();
                        return false;
                    });

            $('#meeting-delete')
                    .click(function () {
                        $.ajax({
                            dataType: 'json',
                            url: '{{ URL::route('booking_delete_ajax') }}',
                            type: "POST",
                            data: {
                                booking_id: activeEvent.booking_id,
                                id: activeEvent.id
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


            $('#meeting-log-time')
                    .click(function () {
                        $.ajax({
                            dataType: 'json',
                            url: '{{ URL::route('booking_log_time_ajax', array('booking_item_id' => 999999)) }}'.replace('999999', activeEvent.id),
                            type: "GET",
                            success: function (data) {
                                if (data.status == 'KO') {
                                    toastr.error(data.message);
                                } else {
                                    toastr.success(data.message);
                                    //activeEvent.is_accounted = true;
                                    activeEvent.event.is_accounted = true;
                                    $('#calendar').fullCalendar('updateEvent', activeEvent.event);
                                    $('#BookingDialog').modal('hide');
                                }
                            },
                            error: function (data) {
                                // afficher un message générique?
                                toastr.error('Erreur inconnue');
                                $('#BookingDialog').modal('hide');
                            }
                        });
                        return false;
                    });


            $('#meeting-view-members-register').click(function () {
                $.ajax({
                    dataType: 'json',
                    url: '{{ URL::route('api_booking_register', array('booking_item_id' => 999999, 'user_id' => Auth::id())) }}'.replace('999999', activeEvent.id),
                    type: "GET",
                    success: function (data) {
                        if (data.status == 'OK') {
                            $('#meeting-view-members-list').append(formatMember(data.member));
                            $('#meeting-view-members h4 span').html($('#meeting-view-members-list > span').length);
                            $('#meeting-view-members-register').hide();
                            $('#meeting-view-members-unregister').show();
                        } else {
                            // error ?
                        }
                    },
                    error: function (data) {
                        // error
                    }
                });
                return false;
            });


            $('#meeting-view-members-unregister').click(function () {
                $.ajax({
                    dataType: 'json',
                    url: '{{ URL::route('api_booking_unregister', array('booking_item_id' => 999999, 'user_id' => Auth::id())) }}'.replace('999999', activeEvent.id),
                    type: "GET",
                    success: function (data) {
                        if (data.status == 'OK') {
                            $('#meeting-view-members-member-' + data.user_id).remove();
                            $('#meeting-view-members h4 span').html($('#meeting-view-members-list > span').length);

                            $('#meeting-view-members-unregister').hide();
                            $('#meeting-view-members-register').show();
                        } else {
                            // error ?
                        }
                    },
                    error: function (data) {
                        // error
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
                            var msg = '';
                            for (field in data.messages) {
                                msg += data.messages[field] + "\n";
                            }
                            alert(msg);
                        } else {
                            for (var i = 0; i < data.events.length; i++) {
                                var events = $('#calendar').fullCalendar('clientEvents', data.events[i].id);
                                if (events.length > 0) {
                                    var event = events[0];
                                    for (k in data.events[i]) {
                                        event[k] = data.events[i][k];
                                    }
                                    $('#calendar').fullCalendar('updateEvent', event);
                                    //console.log('updated ' + data.events[i].id);
                                } else {
                                    $('#calendar').fullCalendar('renderEvent', data.events[i], true);
                                    //console.log('added ' + data.events[i].id);
                                }
                                $('#newBookingDialog').modal('hide');
                            }
                        }
                    },
                    error: function (data) {
                        $('#newBookingDialog').modal('hide');
                    }
                });

                return false;
            });

            $('#calendar').fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'agendaWeek,agendaDay' // month,basicWeek,basicDay,
                },
                eventRender: function (event, element) {
                    if (event.is_private) {
                        element.find(".fc-time")
                                .before($("<span class=\"fa fa-lock pull-right\"></span>"))
                        ;
                    } else {
//                        element.find(".fc-time").before($("<span class=\"fa fa-unlock pull-right\"></span>"));
                    }
                    if (event.is_accounted) {
                        element.find(".fc-time")
                                .before($("<span class=\"fa fa-check-circle pull-right\"></span>"))
                        ;
                    } else {
//                        element.find(".fc-time").before($("<span class=\"fa fa-unlock pull-right\"></span>"));
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
                    activeEvent = new Etincelle.Event();
                    activeEvent.start = start;
                    activeEvent.end = end;
                    activeEvent.edit();
                    $('#calendar').fullCalendar('unselect');
                },
                eventClick: function (calEvent, jsEvent, view) {
                    activeEvent = new Etincelle.Event();
                    activeEvent.populate(calEvent);
                    activeEvent.show();
                    return false;
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


            $('#ressource_filter input[type=checkbox]').on('click', function () {
                if ($(this).is(':checked')) {
                    $('.booking-' + $(this).val()).show();
                } else {
                    $('.booking-' + $(this).val()).hide();
                }
            });

            toastr.options = {
                "closeButton": true,
                "debug": false,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "onclick": null,
                "showDuration": "400",
                "hideDuration": "1000",
                "timeOut": "7000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            }
        })
        ;
    </script>

@stop



