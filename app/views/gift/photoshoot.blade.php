@extends('layouts.master')

@section('meta_title')
    Shooting Photo
@stop


@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>
                Shooting Photo
            </h2>
        </div>

    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            @foreach($data as $session)
                <div class="ibox">
                    <div class="ibox-title">
                        {{date('d/m/Y', strtotime($session->occurs_at))}}
                    </div>
                    <div class="ibox-content">
                        <table class="table table-striped table-hover">
                            @foreach($session->slots as $item)
                                <tr>
                                    <td class="col-md-1">
                                        {{substr($item->start_at, 0, 5)}}
                                    </td>
                                    <td>
                                        @if($item->user_id)
                                            <a href="{{ URL::route('user_profile', $item->user->id) }}">{{ $item->user->fullname }}</a>
                                        @else
                                            <i> -- Disponible --</i>
                                        @endif
                                    </td>
                                    <td class="col-md-2">
                                        @if($item->user_id)
                                            @if($item->user_id == $item->user_id)
                                                <a href="{{URL::route('gift_photoshoot_cancel', $item->id)}}"
                                                   class="btn btn-danger btn-xs">Annuler</a>
                                            @endif
                                        @else
                                            @if($can_book)
                                            <a href="{{URL::route('gift_photoshoot_book', $item->id)}}"
                                               class="btn btn-primary btn-xs">Réserver</a>
                                                @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            @endforeach

        </div>
    </div>


@stop

@section('javascript')
    <script type="text/javascript">
        $().ready(function () {


        });
    </script>
@stop