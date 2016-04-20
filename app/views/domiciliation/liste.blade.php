@extends('layouts.master')

@section('meta_title')
    Entreprises domiciliées
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-12">
            <h2>Entreprises domiciliées</h2>
        </div>
    </div>
@stop

@section('content')
    @if(count($companies)==0)
        <p>Aucune entreprise.</p>
    @else
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="row">
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>Organisation</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($companies as $company)
                                    <tr>
                                        <td>
                                            <a href="{{ URL::route('organisation_modify', $company->id) }}">{{ $company->name }}</a>
                                        </td>
                                        <td>
                                            <a href="{{ URL::route('domiciliation_renew', $company->id) }}"
                                               class="btn btn-xs btn-default">
                                                Renouveller
                                            </a>

                                            <a href="{{ URL::route('organisation_modify', $company->id) }}"
                                               class="btn btn-xs btn-default btn-outline">
                                                Modifier
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="7">{{ $companies->links() }}</td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endif
@stop
