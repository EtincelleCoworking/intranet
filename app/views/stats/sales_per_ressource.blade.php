@extends('layouts.master')

@section('meta_title')
    {{$ressource->location}} - {{$ressource->name}}
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>
                {{$ressource->location}} - {{$ressource->name}}
            </h2>
        </div>

    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    <div class="row">
    @if(count($items) > 0)
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>Période</th>
            <th>CA</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($items as $data)
            <tr>
                <td>{{ $data->occurs_at }}</td>
                <td style="text-align:right">{{ number_format($data->amount, 0, ',', '.') }}€ HT</td>
            </tr>
        @endforeach
        </tbody>
    </table>

@else
    <p>Aucune vente</p>
    @endif

                    </div>
                </div>

            </div>
        </div>
    </div>
@stop




