@extends('layouts.master')

@section('meta_title')
    En attente de facturation
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-8">
            <h2>En attente de facturation</h2>
        </div>
        <div class="col-sm-4">
            <div class="title-action">
            </div>
        </div>
    </div>
@stop

@section('content')

    @if(count($items)==0)
        <p>Aucun élément.</p>
    @else
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="row">
                            <p>Cette page liste les sociétés pour lesquelles des réservations sont en attente de facturation. Seules celles dont au moins une réservation a été comptabilisé mais non encore facturées sont listées ici.</p>
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>Société</th>
                                    <th>Réservations en attente</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($items as $organisation)
                                    <tr>
                                        <td>
                                            <a href="{{ URL::route('organisation_modify', $organisation->id) }}">{{ $organisation->name }}</a>
                                        </td>
                                        <td>
                                            {{$organisation->getCountedBookingCount($period_start, $period_end)}}
                                            <?php
                                            $pending = $organisation->getNotYetCountedBookingCount($period_start, $period_end);
                                            if ($pending) {
                                                printf('<small>(+%d à compter)</small>', $pending);
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <a href="{{ URL::route('pasttime_list') }}?filtre_submitted=1&filtre_organisation_id={{ $organisation->id }}&&filtre_user_id=0&filtre_start={{date('d/m/Y', strtotime($period_start))}}&filtre_end={{date('d/m/Y', strtotime($period_end))}}"
                                               class="btn btn-xs btn-primary">Temps passé</a>
                                            <a href="{{ URL::route('booking_list') }}?filtre_submitted=1&filtre_organisation_id={{ $organisation->id }}&&filtre_user_id=0&filtre_start={{date('d/m/Y', strtotime($period_start))}}&filtre_end={{date('d/m/Y', strtotime($period_end))}}"
                                               class="btn btn-xs btn-default">Réservations</a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endif
@stop

@section('javascript')
    <script type="text/javascript">
        $().ready(function () {

        });
    </script>
@stop
