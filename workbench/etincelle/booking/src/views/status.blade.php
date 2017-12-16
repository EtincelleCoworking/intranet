<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <META http-equiv="refresh" content="60">
    <title>
        @section('meta_title')
            Intranet {{ $_ENV['organisation_name'] }}
        @show
    </title>

    {{ HTML::style('css/bootstrap.min.css') }}
    {{ HTML::style('font-awesome/css/font-awesome.min.css') }}

    {{ HTML::style('css/animate.css') }}
    {{ HTML::style('css/style.css') }}

</head>

<body class="gray-bg">
@if($current_booking)
    <div class="border-left-right p-lg" style="border-color: #EF5352; border-width:50px; height: 100%;">
        <div class="row">
            <div class="col-md-11 h3">{{$ressource->location}}</div>
            <div class="col-md-1 h3 text-right" id="time">{{date('H:i')}}</div>
        </div>
        <div class="jumbotron">
            <div class="h2">{{$ressource->name}}</div>
            <div class="h1">{{$current_booking->title}}</div>
            <div class="h2">
                {{date('H:i', strtotime($current_booking_item->start_at))}}
                -
                {{date('H:i', strtotime($current_booking_item->start_at) + $current_booking_item->duration * 60)}}
            </div>
            <div class="progress" style="height: 100px;">
                <div style="width: {{$current_booking_progress}}%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{$current_booking_progress}}" role="progressbar"
                     class="progress-bar progress-bar-success">
                </div>
                <div style="width: {{100 - $current_booking_progress}}%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{100 - $current_booking_progress}}" role="progressbar"
                     class="progress-bar progress-bar-warning">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 h2">
                @if($next_booking_item)
                    Prochaine réservation à {{date('H:i', strtotime($next_booking_item->start_at))}}
                    - {{$next_booking->title}}
                @else
                    Aucune réservation
                @endif
            </div>
        </div>
    </div>
@else
    <div class="border-left-right p-lg" style="border-color: #1ab394; border-width:50px; height: 100%;">
        <div class="row">
            <div class="col-md-11 h3">{{$ressource->location}}</div>
            <div class="col-md-1 h3 text-right" id="time">{{date('H:i')}}</div>
        </div>
        <div class="jumbotron">
            <div class="h2">{{$ressource->name}}</div>
            <div class="h1">Disponible</div>
            <div class="h2">
                @if($next_booking_item)
                    Jusqu'à {{date('H:i', strtotime($next_booking_item->start_at))}} ({{$free_duration}})
                @else
                    Disponible toute la journée
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 h2">
                @if($next_booking_item)
                    Prochaine réservation : {{$next_booking->title}}
                    ({{date('H:i', strtotime($next_booking_item->start_at))}}
                    -
                    {{date('H:i', strtotime($next_booking_item->start_at) + $next_booking_item->duration * 60)}})
                @endif
            </div>
        </div>
    </div>
@endif

{{ HTML::script('js/jquery-2.1.1.js') }}
{{ HTML::script('js/bootstrap.min.js') }}

<script type="application/javascript">
    function updateClock() {
        var now = new Date();
        document.getElementById('time').innerHTML = now.getHours() + ':' + now.getMinutes();

        setTimeout(updateClock, 1000);
    }

   // updateClock(); // initial call
</script>

</body>

</html>




