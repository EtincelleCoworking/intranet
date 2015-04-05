@extends('layouts.master')

@section('content')
	<h1>Tableau de bord</h1>

    <div class="row">
        <div class="col-md-4">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="fa fa-money fa-2x"></i>
                        <span class="pull-right">
                            @if (Auth::user()->role == 'superadmin')
                            CA
                            @else
                            Factures
                            @endif
                        du mois</span>
                    </h3>
                </div>
                <div class="panel-body">
                    <h3 align="center">{{ (($totalMonth) ? $totalMonth->total : 0) }}€</h3>
                </div>
            </div>
            @if (Auth::user()->role == 'superadmin')
            <div class="panel panel-danger">
                <div class="panel-heading">
                    <h3 class="panel-title"><i class="fa fa-file fa-2x"></i> <span class="pull-right">Charges du mois</span></h3>
                </div>
                <div class="panel-body">
                    <h3 align="center">
                        {{ (($chargesMonth) ? $chargesMonth->total : 0) }}€
                        @if ($chargesMonthToPay && $chargesMonthToPay->total)
                        <br />
                        dont reste dû : {{ $chargesMonthToPay->total }}€
                        @endif
                    </h3>
                </div>
            </div>
            @endif
        </div>
        <div class="col-md-4">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Récapitulatif des temps passés</h3>
                </div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Ressource</th>
                                <th>Temps passé</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pasttimes as $pasttime)
                            <tr>
                                <td>{{ $pasttime->name }}</td>
                                <td>
                                    @if ($pasttime->hours)
                                        {{ $pasttime->hours.Lang::choice('messages.times_hours', $pasttime->hours) }}
                                    @endif
                                    @if ($pasttime->minutes)
                                        {{ $pasttime->minutes.Lang::choice('messages.times_minutes', $pasttime->minutes) }}
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
				@if ($chooseMember)
				<div class="col-md-4">
					<div class="panel panel-primary">
						<div class="panel-heading">
							<h3 class="panel-title">Un membre au hasard</h3>
						</div>
						<div class="panel-body">
							<h4 align="center">{{ $chooseMember->fullname }}</h4>
							@if ($chooseMember->avatar)
									{{ HTML::image('uploads/avatars/'.$chooseMember->avatar, '', array('class' => 'profile-avatar-img thumbnail')) }}
							@else
									{{ HTML::image('img/avatars/avatar.png', '', array('class' => 'profile-avatar-img thumbnail')) }}
							@endif

							@foreach ($chooseMember->all_skills['major'] as $key=>$skill)
								@if ($key != 0)
									,
								@endif
								{{ $skill['name'] }} ({{ $skill['value'] }}%)
							@endforeach
							@if ($chooseMember->all_skills['major'])
								,
							@endif
							{{ $chooseMember->all_skills['minor'] }}

							<div align="center"><a href="{{ URL::route('user_profile', $chooseMember->id) }}" class="btn btn-default">Consulter son profil</a></div>
						</div>
					</div>
				</div>
				@endif
    </div>

@stop
