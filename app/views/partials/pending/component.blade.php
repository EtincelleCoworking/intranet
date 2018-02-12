@if(Auth::user()->isSuperAdmin())
    <?php
    $pending = InvoiceItem::Pending();
    $on_hold = InvoiceItem::OnHold();
    ?>
    <div class="widget style2 yellow-bg">
            <div class=" text-center">
                <span> Encours Clients</span>

                <h2 class="font-bold">
                    {{ number_format($pending['total'], 0, ',', '.') }}€
                </h2>
                @if($on_hold['total']>0)
                    <small>+ {{ number_format($on_hold['total'], 0, ',', '.') }}€ en compte</small>
                @endif
            </div>
    </div>
@endif