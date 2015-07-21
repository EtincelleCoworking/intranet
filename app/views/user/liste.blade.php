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
                    <a href="{{ URL::route('user_add') }}" class="btn btn-default">Ajouter un membre</a>
                </div>
            @endif
        </div>
    </div>
@stop

@section('content')

    <div class="row">
        @foreach ($users as $index => $user)
            <div class="col-md-3">

                {{--<div class="ibox" style="border-top: 5px solid #542274">--}}
                {{--<div class="ibox" style="border-top: 5px solid #FF9930">--}}
                <div class="ibox">
                    <div class="ibox-content text-center">
                        <h1>{{ $user->fullname }}</h1>

                        <div class="m-b-sm">
                            {{$user->avatarTag}}
                        </div>
                        @if($user->bio_short)
                            <p class="font-bold">{{ $user->bio_short }}</p>
                        @endif

                        <div class="text-center">
                            @if($user->phone)
                                <span class="btn btn-xs btn-white"><i
                                            class="fa fa-phone"></i> {{ $user->phone }}</span>
                            @endif

                            @if($user->twitter)
                                <a class="btn btn-xs btn-white" href="https://twitter.com/{{ $user->twitter }}"><i
                                            class="fa fa-twitter"></i> {{ '@'.$user->twitter }} </a>
                            @endif

                            @if($user->website)
                                <a href="https://twitter.com/{{ $user->website }}" class="btn btn-xs btn-white">
                                    <i class="fa fa-link" title="{{ $user->website }}"></i> {{ $user->website }}
                                </a>
                            @endif
                        </div>
                        @if (Auth::user()->role == 'superadmin')
                            <a href="{{URL::route('user_modify', $user->id)}}" class="btn btn-default">Modifier</a>
                        @endif
                    </div>
                </div>
            </div>

        @endforeach
    </div>

    {{ $users->links() }}
@stop