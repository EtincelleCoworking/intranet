@extends('layouts.master')

@section('meta_title')
    Abonnements - Dépassements
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>Liste des dépassements sur les abonnements</h2>
        </div>
    </div>
@stop

@section('content')

    @if(count($subscriptions) == 0)
        <div class="middle-box text-center animated fadeInRightBig">
            <h3 class="font-bold">Aucun dépassement</h3>

        </div>
    @else


        <div class="row">
            <div class="col-lg-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-content">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Espace</th>
                                <th>Période</th>
                                <th>Membre</th>
                                <th>Usage</th>
                                <th>Dépassement</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($subscriptions as $data)
                                <tr
                                        @if($data->subscription_overuse_managed)
                                        class="text-muted"
                                        @elseif($data->overuse > 20)
                                        class="danger"
                                        @endif
                                >
                                    <td>{{$data->location}}</td>
{{--
                                    <td>{{date('d/m/Y', strtotime($data->date_invoice))}}</td>
--}}
                                    <td>
                                        {{date('d/m/Y', strtotime($data->subscription_from ))}}
                                        au {{date('d/m/Y', strtotime('-1 day', strtotime($data->subscription_to)))}}
                                    </td>
                                    <td>
                                        <a href="{{ URL::route('user_modify', $data->user_id) }}">{{ $data->username }}</a>
                                    </td>
                                    <td>
                                        @if($data->hours||$data->minutes)
                                            @if ($data->hours)
                                                {{ $data->hours }} h
                                            @endif
                                            @if ($data->minutes)
                                                {{ $data->minutes }} min
                                            @endif
                                        @else
                                            0 h
                                        @endif
                                        @if($data->ordered > 0)
                                            / {{$data->ordered}} h
                                        @else
                                            / Illimité
                                        @endif
                                    </td>
                                    <td align="right">
                                        @if($data->overuse>0)
                                            @if($data->overuse > 20)
                                                <span class="text-danger">
                                        {{$data->overuse}}%
                                                </span>
                                            @else
                                                {{$data->overuse}}%
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$data->subscription_overuse_managed)
                                            <a class="btn btn-xs btn-default"
                                               href="{{ URL::route('subscription_overuse_managed', $data->invoices_items_id) }}">Noter comme traitée</a>
                                            @endif
                                    </td>
                            @endforeach
                            </tbody>
                        </table>
                        @endif
                    </div>
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
