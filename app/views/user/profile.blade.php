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
                    {{ HTML::image('img/avatars/avatar.png', '', array('class' => 'profile-avatar-img thumbnail')) }}
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
                <li><i class="icon-li fa fa-envelope"></i> {{ HTML::mailto($user->email, 'Envoyer un email') }}</li>
                @if ($user->website)
                  <li><i class="icon-li fa fa-globe"></i> {{ link_to($user->website) }}</li>
                @endif
                @if ($user->twitter)
                  <li><i class="icon-li fa fa-twitter"></i> {{ link_to('http://twitter.com/'.$user->twitter) }}</li>
                @endif
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
        @foreach ($user->all_skills['major'] as $key=>$skill)
          <div class="col-md-3" align="center">
              <h4>{{ $skill['name'] }}</h4>
              <canvas class="chart-competence" id="chart-competence{{ $key }}" width="150px" data-value="{{ $skill['value'] }}" />
          </div>
        @endforeach
    </div>
    <div class="row">
      <div class="col-md-12">
        <br />
        <p>Autres compétences : {{ $user->all_skills['minor'] }}</p>
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
        var nbComp = $('.chart-competence').length;

        for (var $i = 0; $i < nbComp; $i++) {
          var comp$i = $('#chart-competence'+$i).data('value');
          var comp$i_data = data;
          comp$i_data[0]['value'] = comp$i;
          comp$i_data[1]['value'] = 100-comp$i;

          var ctx$i = document.getElementById("chart-competence"+$i).getContext("2d");
          window.myCompetence$i = new Chart(ctx$i).Doughnut(data);
        }


    };
    </script>
@stop
