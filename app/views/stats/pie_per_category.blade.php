@extends('layouts.master')

@section('meta_title')
    Statistiques &gt; Répartition de l'activité
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>
                Statistiques &gt; Répartition de l'activité
                @if($period)
                    - {{ date('m/Y', $period) }}
                @endif
            </h2>
        </div>

    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Répartition de l'activité entre les différentes catégories</h5>
                </div>
                <div class="ibox-content">
                    @foreach($locations as $id => $location_name)
                        <a href="{{URL::route('stats_sales_per_category_and_location', array('location_id'=> $id, 'period' => date('Y-m-d', $period)))}}" class="btn btn-xs
                         @if($id == $location_id)
                         btn-primary
@else
                                btn-default
                                @endif">
                            {{$location_name}}
                        </a>
                        @endforeach

                    @include('stats._pie', array('data' => $data))
                </div>
            </div>
        </div>
    </div>


@stop




