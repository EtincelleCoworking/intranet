@extends('layouts.master')

@section('meta_title')
    Utilisateurs
@stop

@section('breadcrumb')
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-8">
            <h2>Affiliation - {{Auth::user()->fullname}}</h2>
        </div>
        <div class="col-sm-4">
        </div>
    </div>
@stop

@section('content')

    @foreach($items as $year => $data)
        <?php
        $total_per_month = array();
        for ($i = 1; $i <= 12; $i++) {
            $total_per_month[$i] = 0;
        }
        ?>

        <div class="row">
            <div class="col-lg-12">
                @if(count($users))
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
                                        printf('<th class="" width="5%%">%02d</th>', $i);
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
                                            <a href="{{ URL::route('user_modify', $user->id) }}">{{ $user->fullnameOrga }}</a>
                                        </td>
                                        <td>
                                            {{$user->created_at->format('d/m/Y')}}
                                        </td>
                                        <?php for ($i = 1; $i <= 12; $i++) {
                                            if (isset($data[$user->id][$i])) {
                                                printf('<td><small>%s€</small></td>', number_format($data[$user->id][$i], 2, ',', ' '));
                                                $total_per_month[$i] += $data[$user->id][$i];
                                            } else {
                                                printf('<td>-</td>');
                                            }
                                        }
                                        ?>

                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <th colspan="2"></th>
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
                @else
                    <p>Aucun affilié</p>
                @endif
            </div>
        </div>
    @endforeach
@stop

@section('javascript')

    <script type="text/javascript">
        $().ready(function () {

        });
    </script>
@stop