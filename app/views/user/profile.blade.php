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
                        <a href="{{ URL::route('user_modify', $user->id) }}" class="btn btn-success">Modifier</a>
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

            <div class="ibox ">
                <div class="ibox-title">
                    <h5>Contact</h5>
                </div>
                <div class="ibox-content">
                    <p><i class="fa fa-envelope"></i> {{ HTML::mailto($user->email) }}</p>
                    @if ($user->website)
                        <p><i class="fa fa-globe"></i> {{ link_to($user->website) }}</p>
                    @endif
                    @if ($user->twitter)
                        <p>
                            <i class="fa fa-twitter"></i>
                            <a href="http://twitter.com/{{$user->twitter}}">{{ $user->twitter }}</a>
                        </p>
                    @endif
                    @if ($user->birthday != '0000-00-00')
                        <p>
                            <i class="fa fa-birthday-cake"></i>
                            {{ date('d/m/Y', strtotime($user->birthday)) }}
                        </p>
                    @endif
                </div>
            </div>

        </div>
        @if ($user->bio_long)
            <div class="col-lg-9">
                <div class="ibox ">
                    <div class="ibox-title ">
                        <h2>Présentation</h2>
                    </div>
                    <div class="ibox-content">
                        {{ nl2br($user->bio_long)}}

                    </div>
                </div>
            </div>
        @endif
    </div>

@stop

@section('javascript')

@stop
