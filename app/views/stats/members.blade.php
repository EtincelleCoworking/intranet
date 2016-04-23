@extends('layouts.master')

@section('meta_title')
    Evolution des membres
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Evolution des membres</h2>
        </div>

    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5></h5>
                </div>
                <div class="ibox-content">
                    <table class="table">
                        @foreach($items as $period => $data)
                            <tr>
                                <td>{{ $period}}</td>
                                <td>
                                    <table class="table">


                                    @if(isset($data['leaving']))
                                        <tr>
                                            <td>Départs</td>
                                            <td>
                                        @foreach($data['leaving'] as $user_id => $user)
                                            @if($user)
                                                <a href="{{URL::route('user_profile', $user->id)}}">
                                                    <img alt="{{$user->fullname}}" class="img-circle circle-border m-t-xs" style="border-color: #ED5565" src="{{$user->getAvatarUrl(50)}}" title="{{$user->fullnameOrga}}">
                                                </a>
                                            @else
                                                {{$user_id}}
                                            @endif
                                        @endforeach
                                            </td>
                                        </tr>
                                    @endif

                                    @if(isset($data['new-leaving']))
                                            <tr>
                                                <td>Ephémères</td>
                                                <td>
                                        @foreach($data['new-leaving'] as $user_id => $user)
                                            @if($user)
                                                <a href="{{URL::route('user_profile', $user->id)}}">
                                                    <img alt="{{$user->fullname}}" class="img-circle circle-border m-t-xs" style="border-color: #F8AC59" src="{{$user->getAvatarUrl(50)}}" title="{{$user->fullnameOrga}}">
                                                </a>
                                            @else
                                                {{$user_id}}
                                            @endif
                                        @endforeach
                                                </td>
                                            </tr>
                                    @endif

                                    @if(isset($data['members']))
                                            <tr>
                                                <td>Membres</td>
                                                <td>
                                        @foreach($data['members'] as $user_id => $user)
                                            @if($user)
                                                <a href="{{URL::route('user_profile', $user->id)}}">
                                                    <img alt="{{$user->fullname}}" class="img-circle circle-border m-t-xs" style="border-color: #23C6C8" src="{{$user->getAvatarUrl(50)}}" title="{{$user->fullnameOrga}}">
                                                </a>
                                            @else
                                                {{$user_id}}
                                            @endif
                                        @endforeach
                                            </td>
                                                    </tr>
                                    @endif

                                    @if(isset($data['new']))
                                            <tr>
                                                <td>Nouveaux</td>
                                                <td>
                                        @foreach($data['new'] as $user_id => $user)
                                            @if($user)
                                                <a href="{{URL::route('user_profile', $user->id)}}">
                                                    <img alt="{{$user->fullname}}" class="img-circle circle-border m-t-xs" style="border-color: #1AB394" src="{{$user->getAvatarUrl(50)}}" title="{{$user->fullnameOrga}}">
                                                </a>
                                            @else
                                                {{$user_id}}
                                            @endif
                                        @endforeach
                                            </td>
                                                            </tr>
                                    @endif
                                    </table>

                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>


@stop




