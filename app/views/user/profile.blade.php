@extends('layouts.master')

@section('meta_title')
    Profil de {{ $user->fullname }}
@stop

@section('content')
    <div class="row">
        <div class="col-md-3 col-sm-5">
            <div class="profile-avatar">
                @if ($user->avatar)
                    {{ HTML::image('uploads/avatars/'.$user->avatar, '', array('class' => 'profile-avatar-img thumbnail')) }}
                @else
                    {{ HTML::image('img/avatars/avatar-2-lg.jpg', '', array('class' => 'profile-avatar-img thumbnail')) }}
                @endif
            </div> <!-- /.profile-avatar -->
            @if (Auth::user()->id == $user->id)
            <div align="center">
                <a href="{{ URL::route('user_edit') }}" class="btn btn-success">Editer mon profil</a>
            </div>
            @endif
        </div> <!-- /.col -->

        <div class="col-md-6 col-sm-7">
            <h3>{{ $user->fullname }}</h3>
            <h5 class="text-muted">{{ $user->bio_short }}</h5>

            <hr>

            <ul class="icons-list">
                <li><i class="icon-li fa fa-envelope"></i> {{ $user->email }}</li>
                <li><i class="icon-li fa fa-globe"></i> {{ link_to($user->website) }}</li>
                <li><i class="icon-li fa fa-twitter"></i> twitter.com/{{ $user->twitter }}</li>
            </ul>

            <br>

            <p>{{ nl2br($user->bio_long) }}</p>
        </div>
    </div>

    <div class="row" style="margin-top:10px;">
        <div class="col-md-12" align="center">
            <h3>Ses compétences</h3>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3" align="center">
            <h4>{{ $user->competence1_title }}</h4>
            <canvas id="chart-competence1" width="150px" data-value="{{ $user->competence1_value }}" />
        </div>
        <div class="col-md-3" align="center">
            <h4>{{ $user->competence2_title }}</h4>
            <canvas id="chart-competence2" width="150px" data-value="{{ $user->competence2_value }}" />
        </div>
        <div class="col-md-3" align="center">
            <h4>{{ $user->competence3_title }}</h4>
            <canvas id="chart-competence3" width="150px" data-value="{{ $user->competence3_value }}" />
        </div>
        <div class="col-md-3" align="center">
            <h4>{{ $user->competence4_title }}</h4>
            <canvas id="chart-competence4" width="150px" data-value="{{ $user->competence4_value }}" />
        </div>
    </div>
@stop

@section('javascript')
    {{ HTML::script('js/Chart.min.js') }}
    <script type="text/javascript">
    var data = [
        {
            value: 0,
            color: "#46BFBD",
            highlight: "#5AD3D1",
            label: ""
        },
        {
            value: 0,
            color:"#ccc",
            highlight: "#FF5A5E",
            label: ""
        }
    ];
    window.onload = function(){
        var comp1 = $('#chart-competence1').data('value');
        var comp1_data = data;
        comp1_data[0]['value'] = comp1;
        comp1_data[1]['value'] = 100-comp1;

        var ctx1 = document.getElementById("chart-competence1").getContext("2d");
        window.myCompetence1 = new Chart(ctx1).Doughnut(data);

        var comp2 = $('#chart-competence2').data('value');
        var comp2_data = data;
        comp2_data[0]['value'] = comp2;
        comp2_data[1]['value'] = 100-comp2;

        var ctx2 = document.getElementById("chart-competence2").getContext("2d");
        window.myCompetence2 = new Chart(ctx2).Doughnut(data);

        var comp3 = $('#chart-competence3').data('value');
        var comp3_data = data;
        comp3_data[0]['value'] = comp3;
        comp3_data[1]['value'] = 100-comp3;

        var ctx3 = document.getElementById("chart-competence3").getContext("2d");
        window.myCompetence3 = new Chart(ctx3).Doughnut(data);

        var comp4 = $('#chart-competence4').data('value');
        var comp4_data = data;
        comp4_data[0]['value'] = comp4;
        comp4_data[1]['value'] = 100-comp4;

        var ctx4 = document.getElementById("chart-competence4").getContext("2d");
        window.myCompetence4 = new Chart(ctx4).Doughnut(data);
    };
    </script>
@stop