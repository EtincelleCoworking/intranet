@if(Auth::user()->isSuperAdmin())
    <?php
    $pending = InvoiceItem::Pending();
    $on_hold = InvoiceItem::OnHold();
    ?>
    <div class="widget style1 yellow-bg">
        <div class="row">
            <div class="col-xs-4">
                <i class="fa fa-pause fa-5x"></i>
            </div>
            <div class="col-xs-8 text-right">
                <span> Encours Clients</span>

                <h2 class="font-bold">
                    {{ number_format($pending['total'], 0, ',', '.') }}€
                </h2>
                @if($on_hold['total']>0)
                    <small>+ {{ number_format($on_hold['total'], 0, ',', '.') }}€ en compte</small>
                @endif
            </div>
        </div>
    </div>
@endif