@extends('layouts.master')

@section('meta_title')
    Réservations
@stop


<?php $ressources = Ressource::whereIsBookable(true)
    ->join('locations', 'ressources.location_id', '=', 'locations.id')
    ->where('locations.city_id', '=', Auth::user()->location->city_id)
    ->select('ressources.*')
    ->orderBy('locations.name', 'asc')
    ->orderBy('ressources.order_index', 'asc')
    ->get();

$ressources_by_space = array();
foreach ($ressources as $ressource) {
    $ressources_by_space[$ressource->location->name][] = $ressource;
}
?>

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-10">
            <h2>Réservations</h2>
            @if(count($ressources)>1)
                <form id="ressource_filter" action="#" autocomplete="false">
                    @if(count($ressources_by_space)>1)
                        @foreach($ressources_by_space as $locationName => $ressources)
                            <div class="col-md-6">
                                <fieldset>
                                    <legend>{{$locationName}}</legend>
                                    @include('ressource._ressources', array('ressources' => $ressources))
                                </fieldset>
                            </div>
                        @endforeach
                    @else
                        @include('ressource._ressources', array('ressources' => array_shift($ressources_by_space)))
                    @endif
                </form>
            @endif
        </div>
        <div class="col-sm-2">
            @if(count($ressources)>0)
                <div class="title-action">
                    <a href="{{route('booking_new')}}" class="btn btn-primary" id="meeting-add">Nouvelle réservation</a>
                </div>
            @endif
        </div>

    </div>
@stop

@section('content')
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
                    <a href="#" class="btn btn-default btn-outline" id="meeting-quote">Devis</a>
                    <a href="#" class="btn btn-default btn-outline" id="meeting-modify">Modifier</a>
                    {{--<a href="#" class="btn btn-default btn-outline" id="meeting-duplicate">Dupliquer</a>--}}

                    <a href="#" class="btn btn-primary" id="meeting-confirm">Confirmer</a>

                    <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
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
    {{ HTML::style('css/plugins/fullcalendar/fullcalendar.min.css') }}
    {{ HTML::style('css/plugins/fullcalendar/fullcalendar.print.min.css', array('media'=> 'print')) }}
    {{ HTML::style('css/scheduler.min.css') }}

    <style type="text/css">
        .fc-event.booking-confirmed {
            border-style: solid;
            border-width: 1px;
        }

        .fc-event.booking-not-confirmed {
            border-style: dotted;
            border-width: 3px;
        }

        @foreach($ressources as $ressource)

.fc-event.booking-ofuscated-{{$ressource->id}}                                     {
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
    {{ HTML::script('js/scheduler.min.js') }}
    {{ HTML::script('js/locale/fr.js') }}

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
                this.title = '{{str_replace("'", "\\'", Auth::user()->fullname_orga)}}';
                this.description = '';
                this.start = moment().add(7, 'days');
                this.end = moment().add(7, 'days').add({{ Config::get('booking::default_meeting_duration', 1) }}, 'hours');
                this.ressource_id = null;
                this.is_private = true;
                this.is_accounted = false;
                this.is_confirmed = {{ Config::get('booking::default_is_confirmed', true)?'true':'false' }};
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
            this.is_confirmed = e.is_confirmed;
            this.is_open_to_registration = e.is_open_to_registration;
            $('#meeting-modify').attr('href', '{{ route('booking_modify', 999999) }}'.replace('999999', this.id));
            $('#meeting-confirm').attr('href', '{{ route('booking_confirm', 999999) }}'.replace('999999', this.id));
        };

        {{--Etincelle.Event.prototype.edit = function () {--}}
                {{--var self = this;--}}
                {{--var $form = $('#meeting-form');--}}
                {{--$form.find('input[name="id"]').val(this.id);--}}
                {{--$form.find('input[name="booking_id"]').val(this.booking_id);--}}
                {{--$form.find('input[name="title"]').val(this.title);--}}
                {{--@if (Auth::user()->isSuperAdmin())--}}
                {{--$('select[name="user_id"]').select2().select2('val', this.user_id);--}}
                {{--@endif--}}
                {{--$form.find('textarea[name="description"]').val(this.description);--}}
                {{--$form.find('input[name="date"]').val(this.start.format('DD/MM/YYYY'));--}}
                {{--$form.find('select[name="start"]').val(this.start.format('HH:mm'));--}}
                {{--$form.find('select[name="end"]').val(this.end.format('HH:mm'));--}}
                {{--$form.find('input[name="rooms[]"]').each(function () {--}}
                {{--$(this).prop('checked', $(this).val() == self.ressource_id);--}}
                {{--});--}}
                {{--$form.find('input[name="is_private"]').prop('checked', this.is_private);--}}
                {{--$form.find('input[name="is_open_to_registration"]').prop('checked', this.is_open_to_registration);--}}

                {{--$('#newBookingDialog').modal('show');--}}
                {{--};--}}

            Etincelle.Event.prototype.getViewLink = function () {
            return '{{ route('booking_item_show', 999999) }}'.replace('999999', this.id);
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

            $dialog.find('.modal-header .modal-title').html('<a href="' + this.getViewLink() + '">' + this.title + '</a>');
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
            $('#meeting-quote').show();
            if (!this.is_confirmed) {
                $('#meeting-confirm').show();
            } else {
                $('#meeting-confirm').hide();
            }
            @else
            $('#meeting-log-time').hide();
            $('#meeting-quote').hide();
            if (this.user_id == {{Auth::id()}}) {
                if (!this.is_confirmed) {
                    $('#meeting-confirm').show();
                } else {
                    $('#meeting-confirm').hide();
                }
            } else {
                $('#meeting-confirm').hide();
            }
            @endif

            $dialog.modal('show');
        }


        var activeEvent;

        $().ready(function () {
//            $('#meeting-add')
//                    .click(function () {
//                        var event = new Etincelle.Event();
//                        event.edit();
//                        return false;
//                    });

//            $('#meeting-modify')
//                    .click(function () {
//                        $('#BookingDialog').modal('hide');
//                        activeEvent.edit();
//                        return false;
//                    });

//            $('#meeting-duplicate')
//                    .click(function () {
//                        $('#BookingDialog').modal('hide');
//                        var newEvent = new Etincelle.Event();
//                        newEvent.populate(activeEvent);
//                        newEvent.id = null;
//                        newEvent.booking_id = null;
//                        newEvent.start.add(7, 'days');
//                        newEvent.end.add(7, 'days');
//                        activeEvent = newEvent;
//                        activeEvent.edit();
//                        return false;
//                    });

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


            $('#meeting-quote')
                .click(function () {
                    window.location.href = '{{ URL::route('booking_quote', array('booking_item_id' => 999999)) }}'.replace('999999', activeEvent.id);
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


            $('#calendar').fullCalendar({
                schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
                defaultView: 'agendaWeek', // agendaWeek
                defaultDate: '{{$now}}',
                nowIndicator: true,
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'agendaWeek,timelineDay' // month,basicWeek,basicDay,
                    //right: 'agendaWeek,agendaDay,timelineDay' // month,basicWeek,basicDay,
                },
                resourceGroupField: 'location',
                resources: [
                        @foreach($ressources_by_space as $location => $ressources)
                        @foreach($ressources as $ressource)
                    {
                        id: 'res{{$ressource->id}}',
                        location: '{{$location}}',
                        title: '{{$ressource->name}}', eventColor: '{{$ressource->labelCss}}',
                        price: '{{(int)$ressource->amount}}€/h'
                    },
                    @endforeach
                    @endforeach
                ],
                resourceColumns: [
                    {
                        labelText: 'Salle',
                        field: 'title',
                        width: '40%'
                    },
                    {
                        labelText: 'Tarif',
                        field: 'price',
                        width: '10%'
                    }
                ],
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
//                    activeEvent = new Etincelle.Event();
//                    activeEvent.start = start;
//                    activeEvent.end = end;
//                    activeEvent.edit();
//                    $('#calendar').fullCalendar('unselect');
                    var startMoment = moment(start);
                    var endMoment = moment(end);

                    window.location.href = '{{ URL::route('booking_new_full', array('start_at' => 999999, 'end_at' => 888888)) }}'
                        .replace('999999', startMoment.format('YYYY-MM-DD HH:mm')).replace('888888', endMoment.format('YYYY-MM-DD HH:mm'));

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
                    var new_ressource_id = event.resourceId.replace('res', '');
                    $.ajax({
                        dataType: 'json',
                        url: '{{ URL::route('booking_ajax_update') }}',
                        type: "POST",
                        data: {
                            id: event.id,
                            start: event.start.format(),
                            end: event.end.format(),
                            ressource_id: new_ressource_id
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
                    var e = event;
                    console.log(event);
                    var new_ressource_id = event.resourceId.replace('res', '');
                    $.ajax({
                        dataType: 'json',
                        url: '{{ URL::route('booking_ajax_update') }}',
                        type: "POST",
                        data: {
                            id: event.id,
                            start: event.start.format(),
                            end: event.end.format(),
                            ressource_id: new_ressource_id
                        },
                        success: function (data) {
                            console.log(data);
                            if (data.status == 'KO') {
                                alert(data.message);
                                revertFunc();
                            } else {
                                // ok
                                if (new_ressource_id != e.ressource_id) {
                                    var index = event.className.indexOf('booking-' + e.ressource_id);
                                    if (index !== -1) {
                                        e.className.splice(index, 1);
                                    }
                                    e.className = data.data.className.split(' ');
                                    e.ressource_id = data.data.ressource_id;
                                    e.backgroundColor = data.data.backgroundColor;
                                    e.borderColor = data.data.borderColor;
                                    e.textColor = data.data.textColor;
                                    console.log(e);
                                    $('#calendar').fullCalendar('updateEvent', e);
                                }
                            }
                        },
                        error: function (data) {
                            // afficher un message générique?
                            revertFunc();
                        }
                    });
                }
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



