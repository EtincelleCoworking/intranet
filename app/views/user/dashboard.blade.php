@extends('layouts.master')

@section('content')

    <div class="row">
        <div class="col-lg-9 col-md-8 col-sm-6 col-xs-8">
            <div class="row">
                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
                    @if (Auth::user()->isSuperAdmin())
                        @include('partials.intercom')
                    @endif
                    @include('partials.member.component')
                    @include('partials.slack')
                </div>
                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
                    @include('partials.checkin.availability')
                </div>
                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
                    @include('partials.active_subscription')
                    @include('partials.personnal_code.component')
                </div>
            </div>
            <?php
            $items = DB::select(DB::raw('SELECT count(*) as cnt FROM past_times WHERE date_past < CURRENT_DATE() AND device_id IS NOT NULL AND confirmed IS NULL AND is_free = 0 AND (invoice_id = 0 OR invoice_id IS NULL) AND user_id = ' . Auth::id()));
            if ($items[0]->cnt) {
                echo '<div class="alert alert-warning" role="alert">';
                printf('<p>Vous avez actuellement %d plage horaire qui ont été détectées automatiquement et que vous n\'avez pas confirmées</p>', $items[0]->cnt);
                printf('<p><a href="%s" class="btn btn-warning">Confirmez les maintenant</a></p>', route('pasttime_list'));
                echo '</div>';
            }
            ?>
            @include('partials.wall.component')
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-4">
            {{--
            @include('partials.checkin.component')
            {{--
            @if (Auth::user()->isSuperAdmin())
            @elseif (Auth::user()->role == 'member')
            --}}
            {{--
            @endif
            --}}

            @include('booking::partials.upcoming_events')
            @include('booking::partials.ressource_booking_status')

            @include('partials.next_birthday.component')
        </div>
    </div>

@stop

@section('javascript')
    <?php ; ?>
    {{ HTML::script('js/jquery.waypoints.min.js') }}
    {{ HTML::script('js/infinite.min.js') }}

    @if (Auth::user()->isSuperAdmin())
        <script type="application/javascript">
            function updateIntercomStatus(widget, uri) {
                $.get(uri, function (data) {
                    if ('Yes' == data) {
                        $(widget)
                            .removeClass('label-danger')
                            .addClass('label-primary')
                            .html('ON');
                    } else {
                        $(widget)
                            .removeClass('label-success')
                            .addClass('label-danger')
                            .html('OFF');
                    }
                });
            }

            function updateBoxesStatus() {
                $.get('https://phonebox.etincelle.at/api/status', function (data) {
                    var result = '';
                    for (room in data.data) {
                        result += '<tr><td width="40">';
                        if (room.session.started_at) {
                            result += '<div class="label label-danger">KO</div>';
                        } else {
                            result += '<div class="label label-primary">OK</div>';

                        }
                        result += '</td>';
                        if (room.session.started_at) {
                            result += '<a href="" class="pull-right">'
                                + room.session.user.picture_url +
                                '</a>';
                        }
                        result += room.name + '</td></tr>';
                    }
                    $('#phonebox').innerHTML = result;
                });
            }

            $(function () {
                @foreach(Config::get('etincelle.intercoms') as $key => $data)
                setInterval(function () {
                    updateIntercomStatus('#intercom-{{$key}}', '{{$data['uri']}}')
                }, 60000);

                updateIntercomStatus('#intercom-{{$key}}', '{{$data['uri']}}');
                @endforeach

                setInterval(function () {
                    updateBoxesStatus()
                }, 15000);
            });
        </script>
    @endif

@stop




