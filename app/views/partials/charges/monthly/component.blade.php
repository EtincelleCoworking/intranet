@if(Auth::user()->isSuperAdmin())
    <?php

    if (empty($target_period)) {
        $target_period = date('Y-m');
    }

    $costs = Location::getCostPerLocation();
    $total_cost = 0;
    foreach ($costs as $space => $data) {
        $total_cost += $data[$target_period];
    }
    ?>

    <div class="widget style2 blue-bg">
        <div class="row">
            <div class="{{-- col-xs-8 --}}text-center">
                <span> Coûts du mois </span>

                <h2 class="font-bold">
                    {{ number_format($total_cost , 0, ',', '.') }} €
                </h2>
                <small>&nbsp;</small>
            </div>
        </div>
    </div>
@endif