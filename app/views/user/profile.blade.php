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
            <div align="center">
                <a href="{{ URL::route('user_edit') }}" class="btn btn-success">Editer mon profil</a>
            </div>
        </div> <!-- /.col -->

        <div class="col-md-6 col-sm-7">
            <h3>{{ $user->fullname }}</h3>
            <h5 class="text-muted">{{ $user->bio_short }}</h5>

            <hr>

            <ul class="icons-list">
                <li><i class="icon-li fa fa-envelope"></i> {{ $user->email }}</li>
                <li><i class="icon-li fa fa-globe"></i> {{ $user->website }}</li>
                <li><i class="icon-li fa fa-twitter"></i> twitter.com/{{ $user->twitter }}</li>
            </ul>

            <br>

            <p>{{ nl2br($user->bio_long) }}</p>
        </div>
    </div>
@stop