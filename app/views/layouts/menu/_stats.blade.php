<li class="{{ (Request::is('stats*') || Request::is('admin*')) ? 'active' : '' }}">
    <a href="{{ URL::route('stats_overview') }}"><i class="fa fa-bar-chart-o"></i> <span class="nav-label">Statistiques</span>
        <span class="fa arrow"></span></a>
    <ul class="nav nav-second-level {{  Request::is('stats*')? '' : 'collapse' }}">
        <li{{ Request::is('admin*') ? ' class="active"' : '' }}>
            <a href="{{ URL::route('admin_dashboard') }}">Vue d'ensemble</a>
        </li>
        <li{{ Request::is('stats/overview') ? ' class="active"' : '' }}>
            <a href="{{ URL::route('stats_overview') }}">Evolution CA</a>
        </li>
        <li{{ Request::is('stats/sales_per_category') ? ' class="active"' : '' }}>
            <a href="{{ URL::route('stats_sales_per_category') }}">Par catégorie</a>
        </li>
        <li{{ Request::is('stats/members') ? ' class="active"' : '' }}>
            <a href="{{ URL::route('stats_members') }}">Membres</a>
        </li>
        <li{{ Request::is('stats/sales') ? ' class="active"' : '' }}>
            <a href="{{ URL::route('stats_sales', 'all') }}">Chiffre d'affaires</a>
        </li>
        <li{{ Request::is('stats/customers') ? ' class="active"' : '' }}>
            <a href="{{ URL::route('stats_customers', 'all') }}">Clients</a>
        </li>
        <li{{ Request::is('stats/subscriptions') ? ' class="active"' : '' }}>
            <a href="{{ URL::route('stats_subscriptions') }}">Abonnements</a>
        </li>
        {{--
        <li{{ Request::is('stats/charges') ? ' class="active"' : '' }}>
            <a href="{{ URL::route('stats_charges') }}">Dépenses</a>
        </li>
        --}}
        <li{{ Request::is('stats/age') ? ' class="active"' : '' }}>
            <a href="{{ URL::route('stats_age') }}">Démographie</a>
        </li>
        <li{{ Request::is('stats/spaces') ? ' class="active"' : '' }}>
            <a href="{{ URL::route('stats_spaces') }}">Espaces</a>
        </li>
        <li{{ Request::is('stats/coworking') ? ' class="active"' : '' }}>
            <a href="{{ URL::route('stats_coworking') }}">Coworking</a>
        </li>
        <li{{ Request::is('stats/loyalty') ? ' class="active"' : '' }}>
            <a href="{{ URL::route('stats_loyalty') }}">Fidélité</a>
        </li>
    </ul>

</li>