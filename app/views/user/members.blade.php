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
            @if (Auth::user()->isSuperAdmin())
                <div class="title-action">
                    <a href="{{ URL::route('user_add') }}" class="btn btn-primary">Ajouter un membre</a>
                </div>
            @endif
        </div>
    </div>
@stop

@section('content')

    <div class="row">
        <div id="equalheight">
            @foreach ($users as $index => $user)
                <div class="col-lg-4 col-md-6 col-xs-12">
                    <div class="contact-box">
                        <a href="{{URL::Route('user_profile', $user->id)}}">
                            <div class="col-sm-4">
                                <div class="text-center">
                                    <img alt="image" class="img-circle m-t-xs img-responsive"
                                         src="{{$user->avatarUrl}}">

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
                                        @if($user->social_facebook)
                                            <a href="{{ $user->social_facebook }}">
                                                <i class="fa fa-facebook"></i>
                                            </a>
                                        @endif

                                        @if($user->website)
                                            <a href="{{ $user->website }}">
                                                <i class="fa fa-globe" title="{{ $user->website }}"></i>
                                            </a>
                                        @endif

                                    </p>
                                    @if (Auth::user()->isSuperAdmin())
                                        <a href="{{URL::route('user_modify', $user->id)}}"
                                           class="btn btn-xs btn-default"><i class="fa fa-edit"></i></a>

                                        <a href="{{URL::route('user_login_as', $user->id)}}"
                                           title="Se connecter en tant que {{$user->fullname}}"
                                           class="btn btn-xs btn-default"><i class="fa fa-user-secret"></i></a>

                                        <a href="{{URL::route('user_export_profile', $user->id)}}"
                                           title="Exporter la fiche {{$user->fullname}}"
                                           class="btn btn-xs btn-default"><i class="fa fa-download"></i></a>
                                    @endif

                                </div>
                            </div>
                            <div class="col-sm-8">
                                <a href="{{URL::Route('user_profile', $user->id)}}">
                                    <h3><strong>{{ $user->fullname }}</strong></h3>
                                    @if($user->bio_short)
                                        <p>{{ $user->bio_short }}</p>
                                    @endif


                                    @if($user->phoneFmt)
                                        <i class="fa fa-phone"></i>
                                        {{ $user->phoneFmt }}
                                        <br/>
                                    @endif

                                    @foreach($user->organisations as $company)
                                        @if($company->name != $user->fullname)
                                            <br/>
                                            <i class="fa fa-university"></i>
                                            {{ $company->name }}
                                        @endif
                                    @endforeach
                                </a>

                            </div>
                            <div class="clearfix"></div>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

@stop


@section('javascript')
    <script type="text/javascript">
        $(window).resize(function () {
            $('#equalheight div').equalHeights();
        });
        $(window).resize();
    </script>
@stop
