@extends('layouts.master')

@section('meta_title')
    Affiliation - {{$godfather->fullname}}
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-8">
            <h2>Affiliation - {{$godfather->fullname}}</h2>
            <p>{{$godfather->affiliation_fees}}% du chiffre d'affaire généré par vos filleuls pendant
                les {{$godfather->affiliation_duration}}
                premiers mois de collaboration.</p>

            <p>Si certains de vos filleuls manquent dans la liste, <a href="mailto:support@etincelle-coworking.com?subject=Affiliation">contactez-nous</a> pour les rajouter.</p>
        </div>
        <div class="col-sm-4">
        </div>
    </div>
@stop

@section('content')
    @if(count($users))
        @foreach($items as $year => $data)
            <?php
            $total_per_month = array();
            for ($i = 1; $i <= 12; $i++) {
                $total_per_month[$i] = 0;
            }
            ?>

            <div class="row">
                <div class="col-lg-12">
                    <div class="ibox">
                        <div class="ibox-title">
                            <h5>{{$year}}</h5>
                        </div>
                        <div class="ibox-content">
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th class="" width="30%">Nom</th>
                                    <th class="" width="10%">Créé le</th>
                                    <?php for ($i = 1; $i <= 12; $i++) {
                                        printf('<th class="" width="5%%">%02d/%02d</th>', $i, $year - 2000);
                                    }
                                    ?>
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
                                            @if(Auth::user()->isSuperAdmin())
                                                <a href="{{ URL::route('user_modify', $user->id) }}">{{ $user->fullname }}</a>
                                            @else
                                                {{ $user->fullname }}
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{$user->created_at->format('d/m/y')}}</small>
                                        </td>
                                        <?php for ($i = 1; $i <= 12; $i++) {
                                            if (isset($data[$user->id][$i]['sales'])) {
                                                if ($data[$user->id][$i]['concerned']) {
                                                    $style = 'label-primary';
                                                } else {

                                                    $style = '';
                                                }
                                                printf('<td><small><span class="label %3$s" title="CA = %2$s€">%1$s€ <i class="fa fa-info-circle"></i></span></small></td>', number_format($data[$user->id][$i]['fees'], 2, ',', ' '), number_format($data[$user->id][$i]['sales'], 2, ',', ' '), $style);
                                                $total_per_month[$i] += $data[$user->id][$i]['fees'];
                                            } else {
                                                printf('<td>-</td>');
                                            }
                                        }
                                        ?>

                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>

                                <th colspan="2">Commission</th>
                                <?php
                                for ($i = 1; $i <= 12; $i++) {
                                    if ($total_per_month[$i]) {
                                        printf('<th><small>%s€</small></th>', number_format($total_per_month[$i], 2, ',', ' '));
                                    } else {
                                        printf('<th>-</th>');
                                    }
                                }?>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <p>Aucun affilié</p>
    @endif
@stop

@section('javascript')

    <script type="text/javascript">
        $().ready(function () {

        });
    </script>
@stop