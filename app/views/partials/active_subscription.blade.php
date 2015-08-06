<div class="ibox">
    <div class="ibox-title">
        <h5>Abonnement en cours</h5>
    </div>
    <div class="ibox-content">
        <small>
            Du {{date('d/m/Y', strtotime($active_subscription->subscription_from ))}}
            au {{date('d/m/Y', strtotime('-1 day', strtotime($active_subscription->subscription_to)))}}
        </small>
        <h1 class="no-margins">
            @if($subscription_used)
                @if ($subscription_used->hours)
                    {{ $subscription_used->hours }} h
                @endif
                @if ($subscription_used->minutes)
                    {{ $subscription_used->minutes }} min
                @endif
            @else
                0
            @endif
            @if($active_subscription->subscription_hours_quota > 0)
                / {{$active_subscription->subscription_hours_quota}} h
            @else
                / Illimité
            @endif
        </h1>
        @if($active_subscription->subscription_hours_quota > 0)
            <div class="stat-percent">{{$subscription_ratio}}%</div>
            <div class="progress progress-mini">
                <div style="width: {{$subscription_ratio}}%;" class="progress-bar
                                @if($subscription_ratio > 100)
                        progress-bar-danger
                        @elseif($subscription_ratio>80)
                        progress-bar-warning

                        @endif
                        "></div>
            </div>
        @endif
    </div>
</div>