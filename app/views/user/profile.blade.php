@extends('layouts.master')

@section('meta_title')
    Profil de {{ $user->fullname }}
@stop

@section('content')
    <div class="row">
        <div class="col-md-3 col-sm-5">
            <div class="profile-avatar">
                {{ HTML::image('img/avatars/avatar-2-lg.jpg', '', array('class' => 'profile-avatar-img thumbnail')) }}
            </div> <!-- /.profile-avatar -->
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
@stop