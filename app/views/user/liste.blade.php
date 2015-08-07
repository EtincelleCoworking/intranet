@extends('layouts.master')

@section('meta_title')
    Membres
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Membres</h2>
        </div>
        <div class="col-sm-8">
            @if (Auth::user()->role == 'superadmin')
                <div class="title-action">
                    <a href="{{ URL::route('user_add') }}" class="btn btn-success">Ajouter un membre</a>
                </div>
            @endif
        </div>
    </div>
@stop

@section('content')

    <div class="row">
        @foreach ($users as $index => $user)
            <div class="col-md-3">
                <div class="contact-box">
                    <a href="{{URL::Route('user_profile', $user->id)}}">
                        <div class="col-sm-4">
                            <div class="text-center">
                                <img alt="image" class="img-circle m-t-xs img-responsive" src="{{$user->avatarUrl}}">

                                <p>
                                    @if($user->twitter)
                                        <a href="https://twitter.com/{{ $user->twitter }}">
                                            <i class="fa fa-twitter"></i>
                                        </a>
                                    @endif

                                    @if($user->social_instagram)
                                        <a href="{{ $user->social_instagram }}">
                                            <i class="fa fa-instagram"></i>
                                        </a>
                                    @endif
                                    @if($user->social_github)
                                        <a href="{{ $user->social_github }}">
                                            <i class="fa fa-github"></i>
                                        </a>
                                    @endif
                                    @if($user->social_linkedin)
                                        <a href="{{ $user->social_linkedin }}">
                                            <i class="fa fa-linkedin"></i>
                                        </a>
                                    @endif

                                    @if($user->website)
                                        <a href="{{ $user->website }}">
                                            <i class="fa fa-link" title="{{ $user->website }}"></i>
                                        </a>
                                    @endif

                                </p>
                                @if (Auth::user()->role == 'superadmin')
                                    <a href="{{URL::route('user_modify', $user->id)}}" class="btn btn-xs btn-default">Modifier</a>
                                @endif

                            </div>
                        </div>
                        <div class="col-sm-8">
                            <a href="{{URL::Route('user_profile', $user->id)}}">
                            <h3><strong>{{ $user->fullname }}</strong></h3>
                            @if($user->bio_short)
                                <p>{{ $user->bio_short }}</p>
                            @endif


                            @if($user->phone)
                                <i class="fa fa-phone"></i>
                                {{ $user->phone }}
                                <br/>
                            @endif
</a>

                        </div>
                        <div class="clearfix"></div>
                    </a>
                </div>
            </div>

            @if($index % 4 == 3)
    </div>
    <div class="row">
        @endif

        @endforeach
    </div>

@stop