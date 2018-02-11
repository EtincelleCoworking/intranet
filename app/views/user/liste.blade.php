@extends('layouts.master')

@section('meta_title')
    Utilisateurs
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>Utilisateurs</h2>
        </div>
        <div class="col-sm-8">
            @if (Auth::user()->isSuperAdmin())
                <div class="title-action">
                    <a href="{{ URL::route('user_add') }}" class="btn btn-primary">Ajouter un utilisateur</a>
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
                    <div class="row">
                        {{ Form::open(array('route' => array('user_filter'))) }}
                        {{ Form::hidden('filtre_submitted', 1) }}
                        @if (Auth::user()->isSuperAdmin())
                            <div class="col-md-4">
                                {{ Form::select('filtre_user_id', User::Select('Sélectionnez un client'), Session::get('filtre_user.user_id') ? Session::get('filtre_user.user_id') : null, array('id' => 'filter-client','class' => 'form-control')) }}
                            </div>
                        @else
                            {{ Form::hidden('filtre_user_id', Auth::user()->id) }}
                        @endif

                        <div class="col-md-4 input-group-sm">
                            {{ Form::checkbox('filtre_member', true, Session::has('filtre_user.member') ? Session::get('filtre_user.member') : false) }}
                            Membre<br/>
                            {{ Form::checkbox('filtre_free_coworking_time', true, Session::has('filtre_user.free_coworking_time') ? Session::get('filtre_user.free_coworking_time') : false) }}
                            Invité Coworking<br/>
                            {{ Form::checkbox('filtre_subscription', true, Session::has('filtre_user.subscription') ? Session::get('filtre_user.subscription') : false) }}
                            Souscription active
                        </div>
                        <div class="col-md-4">
                            {{ Form::submit('Filtrer', array('class' => 'btn btn-sm btn-primary')) }}
                            <a href="{{URL::route('user_filter_reset')}}"
                               class="btn btn-sm btn-default">Réinitialiser</a>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>


    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th class="col-md-3">Nom</th>
            <th class="col-md-1">Membre</th>
            <th class="col-md-1">Périphériques</th>
            <th class="col-md-1">Date de naissance</th>
            <th class="col-md-1">Abonnement</th>
            @if(!empty($_ENV['slack_url']))
                <th class="col-md-1">Slack</th>
            @endif
            <th class="col-md-1">Temps passé</th>
            <th class="col-md-1">Invité Coworking</th>
            <th class="col-md-2">Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($users as $user)
            <tr
                    @if(!$user->is_enabled)
                    class="text-muted"
                    @endif
            >
                <td>
                    @if(!$user->is_enabled)
                        <i class="fa fa-ban" title="Compte désactivé"></i>
                    @endif


                    <?php
                    switch ($user->gender) {
                        case 'F':
                            echo '<i class="fa fa-female"></i>';
                            break;
                        case 'M':
                            echo '<i class="fa fa-male"></i>';
                            break;
                        default:
                            echo '<i class="fa fa-question"></i>';
                    }
                    ?>
                    <a href="{{ URL::route('user_modify', $user->id) }}">{{ $user->fullnameOrga }}</a>
                </td>
                <td>
                    <?php
                    if ($user->is_member) {
                        echo 'Oui';
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
                <td>
                    <?php
                    $active_device = false;
                    foreach ($user->devices as $device) {
                        if ($device->last_seen_at && (strtotime($device->last_seen_at) > strtotime('-1 month'))) {
                            $active_device++;
                        }
                    }

                    if ($active_device) {
                        printf('<span class="badge badge-success">%d / %d</span>', $active_device, count($user->devices));
                    } else {
                        if (count($user->devices)) {
                            printf('<span class="badge badge-warning">%d / %d</span>', $active_device, count($user->devices));
                        } else {
                            printf('<span class="badge badge-danger">0</span>');
                        }
                    }

                    ?>
                </td>
                <td>
                    <?php
                    if ($user->birthday && $user->birthday != '0000-00-00') {
                        echo date('d/m/Y', strtotime($user->birthday));
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
                <td>
                    <?php
                    $subscription = $user->getLastSubscription();
                    $duration = 0;
                    if (!$subscription) {
                        printf('-');
                    } else {

                        $remainingDays = (strtotime($subscription['subscription_to']) - time()) / (24 * 3600);

                        if ($remainingDays < -30) {
                            $status = 'badge badge-plain';
                        } elseif ($remainingDays < 0) {
                            $status = 'badge badge-danger';
                        } elseif ($remainingDays < 7) {
                            $status = 'badge badge-warning';
                        } elseif ($remainingDays < 7) {
                            $status = 'badge badge-success';
                        } else {
                            $status = '';
                        }
                        $ratio = '';
                        $duration = 0;
                        if ($subscription['subscription_hours_quota'] != 0) {
                            $duration = $user->getCoworkingTimeSpent($subscription['subscription_from'], $subscription['subscription_to']);
                            if ($subscription['subscription_hours_quota'] != -1) {
                                $ratio = sprintf(' (%d%%)',
                                    100 * $duration / ($subscription['subscription_hours_quota'] * 60));
                            }
                        }
                        printf('<span class="%s">%s</span>%s', $status,
                            date('d/m/Y', strtotime($subscription['subscription_to'])), $ratio);

                    }

                    ?>
                </td>
                @if(!empty($_ENV['slack_url']))
                    <td><?php
                        if ($user->slack_invite_sent_at) {
                            echo date('d/m/Y', strtotime($user->slack_invite_sent_at));
                        }
                        printf('<a href="%s" class="btn btn-xs btn-primary slack-invite">Inviter</a>', URL::route('user_invite_slack', $user->id));
                        ?>
                    </td>
                @endif
                <td>
                    <?php
                    if ($subscription) {

                        if ($duration) {
                            echo durationToHuman($duration);
                        } else {
                            echo '0';
                        }
                        if ($subscription['subscription_hours_quota'] == -1) {
                            echo ' / Illimité';
                        } else {
                            printf(' / %d heures', $subscription['subscription_hours_quota']);
                        }

                    }
                    ?>
                </td>
                <td>
                    @if($user->free_coworking_time)
                        Oui
                    @else
                        -
                    @endif
                </td>
                <td>
                    <a href="{{ URL::route('user_profile', $user->id) }}"
                       class="btn btn-xs btn-primary">Voir</a>
                    <a href="{{ URL::route('user_affiliate', $user->id) }}"
                       class="btn btn-xs btn-default">Affiliation</a>
                    <a href="{{ URL::route('user_modify', $user->id) }}"
                       class="btn btn-xs btn-default">Modifier</a>
                    <a href="{{URL::route('user_login_as', $user->id)}}"
                       title="Se connecter en tant que {{$user->fullname}}"
                       class="btn btn-xs btn-default"><i class="fa fa-user-secret"></i></a>
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <td colspan="7">{{ $users->links() }}</td>
        </tr>
        </tfoot>
    </table>
@stop

@section('javascript')

    <script type="text/javascript">
        $().ready(function () {

            $('#filter-client').select2();

            $('.slack-invite').on('click', function () {
                var link = $(this);
                $.ajax({
                    url: link.attr('href')
                }).done(function (data) {
                    link.replaceWith(data);
                }).error(function (data) {

                });
                return false;
            });
        });
    </script>
@stop