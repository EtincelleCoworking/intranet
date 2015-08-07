@extends('layouts.master')


@section('meta_title')
    Profil de {{ $user->fullname }}
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-8">
            <h2>{{ $user->fullname }}</h2>
            {{$user->bio_short}}
        </div>
        <div class="col-sm-4">
            <div class="title-action">
                @if ((Auth::user()->role == 'superadmin') or (Auth::user()->id == $user->id))
                    <a href="{{ URL::route('user_modify', $user->id) }}" class="btn btn-success pull-right">Modifier</a>
                @endif
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-3">
            <div class="ibox ">

                <div class="ibox-content no-padding border-left-right">

                    @if ($user->avatarUrl)
                        {{ HTML::image($user->largeAvatarUrl, '', array('class' => ' img-responsive')) }}
                    @else
                        {{ HTML::image('/img/avatars/avatar.png', '', array('class' => ' img-responsive')) }}
                    @endif


                </div>
            </div>


        </div>
        <div class="col-lg-9">
            @if ($user->bio_long)
                <div class="ibox ">
                    <div class="ibox-title ">

                        <h2>Présentation</h2>

                    </div>
                    <div class="ibox-content">
                        {{ nl2br($user->bio_long)}}

                    </div>
                </div>
            @endif
            <div class="ibox ">
                <div class="ibox-title">
                    <h5>Contact</h5>
                    @if ($user->birthday != '0000-00-00')
                        <div class="pull-right">
                            <small>
                                <i class="fa fa-birthday-cake"></i>
                                {{ date('d/m/Y', strtotime($user->birthday)) }}
                            </small>
                        </div>
                    @endif
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-lg-4">
                            <p><i class="fa fa-envelope"></i> {{ HTML::mailto($user->email) }}</p>
                        </div>
                        @if ($user->website)
                            <div class="col-lg-4">
                                <p><i class="fa fa-globe"></i> {{ link_to($user->website) }}</p>
                            </div>
                        @endif
                        @if ($user->twitter)
                            <div class="col-lg-4">
                                <p>
                                    <i class="fa fa-twitter"></i>
                                    <a href="http://twitter.com/{{$user->twitter}}">{{ $user->twitter }}</a>
                                </p>
                            </div>
                        @endif
                        @if ($user->social_github)
                            <div class="col-lg-4">
                                <p>
                                    <i class="fa fa-github"></i>
                                    <a href="{{$user->social_github}}">{{ $user->social_github }}</a>
                                </p>
                            </div>
                        @endif
                        @if ($user->social_instagram)
                            <div class="col-lg-4">
                                <p>
                                    <i class="fa fa-instagram"></i>
                                    <a href="{{$user->social_instagram}}">{{ $user->social_instagram }}</a>
                                </p>
                            </div>
                        @endif

                        @if ($user->social_linkedin)
                            <div class="col-lg-4">
                                <p>
                                    <i class="fa fa-linkedin"></i>
                                    <a href="{{$user->social_linkedin}}">{{ $user->social_linkedin }}</a>
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

@stop

@section('javascript')

@stop
