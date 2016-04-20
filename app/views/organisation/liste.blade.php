@extends('layouts.master')

@section('meta_title')
    Liste des sociétés
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Liste des sociétés</h2>
        </div>
        <div class="col-sm-8">
            @if (Auth::user()->isSuperAdmin())
                <div class="title-action">
                    <a href="{{ URL::route('organisation_add') }}" class="btn btn-default">Ajouter une société</a>
                </div>
            @endif
        </div>
    </div>
@stop



@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Filtre</h5>

                    {{--<div class="ibox-tools">--}}
                    {{--<a class="collapse-link">--}}
                    {{--<i class="fa fa-chevron-up"></i>--}}
                    {{--</a>--}}
                    {{--</div>--}}
                </div>
                <div class="ibox-content">

                    {{ Form::open(array('route' => array('organisation_list'))) }}
                    {{ Form::hidden('filtre_submitted', 1) }}
                    <div class="row">
                        <div class="col-md-9">
                            {{ Form::select('filtre_organisation_id', Organisation::Select('Sélectionnez une société'), Session::get('filtre_organisation.organisation_id') ? Session::get('filtre_organisation.organisation_id') : null, array('id' => 'filter-organisation','class' => 'form-control')) }}
                        </div>
                        <div class="col-md-3">
                            {{ Form::submit('Filtrer', array('class' => 'btn btn-sm btn-primary')) }}
                            <a href="{{URL::route('invoice_filter_reset')}}" class="btn btn-sm btn-default">Réinitialiser</a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-content">
                    <div class="row">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Facturation</th>
                                <th>Domiciliation</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($organisations as $organisation)
                                <tr>
                                    <td>
                                        <a href="{{ URL::route('organisation_modify', $organisation->id) }}">{{ $organisation->name }}</a>
                                    </td>
                                    <td>@if($organisation->accountant_id)
                                            <a href="{{URL::route('user_modify', $organisation->accountant_id)}}">
                                                {{ $organisation->accountant->fullname }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $organisation->is_domiciliation?'Oui':'-' }}</td>
                                    <td>
                                        <a href="{{ URL::route('organisation_modify', $organisation->id) }}"" class="btn
                                        btn-xs btn-default">Modifier</a>
                                        <a href="{{ URL::route('invoice_add_organisation', array('D', $organisation->id)) }}"
                                           class="btn btn-xs btn-default btn-outline">Ajouter un devis</a>
                                        <a href="{{ URL::route('invoice_add_organisation', array('F', $organisation->id)) }}"
                                           class="btn btn-xs btn-default btn-outline">Ajouter une facture</a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="6">{{ $organisations->links() }}</td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('javascript')
    <script type="text/javascript">
        $().ready(function () {
            $('#filter-organisation').select2();
        });
    </script>
@stop
