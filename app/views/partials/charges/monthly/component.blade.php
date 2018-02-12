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
    <div class="ibox">
        <div class="ibox-content">
            <h5>Charges du mois</h5>
            <h1 class="no-margins">
                {{ number_format($total_cost , 0, ',', '.') }} €
            </h1>
        </div>
    </div>
@endif