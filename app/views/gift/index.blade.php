@extends('layouts.master')

@section('meta_title')
    Cadeaux pour {{$user->fullname}}
@stop


@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>
                Cadeaux pour {{$user->fullname}}
            </h2>
        </div>

    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox-content">
                <table class="table table-striped table-hover">
                    <thead>
                    <th>Type</th>
                    <th>Activé le</th>
                    <th>Utilisé le</th>
                    <th>Actions</th>
                    </thead>
                    @foreach($kinds as $kind)
                        @if(!isset($user_gifts[$kind->code]))
                            <tr class="text-muted">
                                <td class="col-md-3">
                                    {{$kind->description}}
                                </td>
                                <td class="col-md-3">-</td>
                                <td class="col-md-3">-</td>
                                <td class="col-md-3">
                                    <a href="{{URL::route('user_gift_enable', array('user_id' => $user->id, 'kind' => $kind->code))}}"
                                       class="btn btn-primary btn-xs">Activer</a>
                                </td>
                            </tr>
                        @else
                            <tr>
                                <td class="col-md-3">
                                    {{$kind->description}}
                                </td>
                                <td class="col-md-3">
                                    @if($user_gifts[$kind->code]->created_at)
                                        {{date('d/m/Y H:i', strtotime($user_gifts[$kind->code]->created_at))}}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="col-md-3">
                                    @if($user_gifts[$kind->code]->used_at)
                                        {{date('d/m/Y H:i', strtotime($user_gifts[$kind->code]->used_at))}}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="col-md-3">
                                    <a href="{{URL::route('user_gift_disable', array('user_id' => $user->id, 'kind' => $kind->code))}}"
                                       class="btn btn-danger btn-xs">Désactiver</a>
                                </td>
                            </tr>

                        @endif

                    @endforeach
                </table>
            </div>
        </div>
    </div>
@stop

@section('javascript')
    <script type="text/javascript">
        $().ready(function () {


        });
    </script>
@stop