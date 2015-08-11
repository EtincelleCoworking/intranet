<ul class="nav metismenu" id="side-menu">
    <li class="nav-header">
        <div class="dropdown profile-element">
            <span>
                <img alt="image" class="img-circle img-responsive" src="{{Auth::user()->avatarUrl}}"/>
            </span>
            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                <span class="clear"> <span class="block m-t-xs">
                        <strong class="font-bold">{{Auth::user()->fullname}}</strong>
                </span>
                <span class="text-muted text-xs block">Super Administrateur <b class="caret"></b></span> </span>
            </a>
    <ul class="dropdown-menu animated fadeInRight m-t-xs">
        <li><a href="{{ URL::route('user_edit') }}">Profil</a></li>
        {{--<li><a href="contacts.html">Contacts</a></li>--}}
        {{--<li><a href="mailbox.html">Mailbox</a></li>--}}
        <li class="divider"></li>
        <li><a href="{{ URL::route('user_logout') }}">Déconnexion</a></li>
    </ul>
        </div>
        <div class="logo-element">
            EC
        </div>
    </li>

    <li class="{{ ((Request::is('/')) ? 'active' : '') }}">
        <a href="{{ URL::route('dashboard') }}"><i class="fa fa-th-large"></i> <span class="nav-label">Tableau de bord</span></a>
    </li>


    <li{{ ((Request::is('user*') || Request::is('organisation*')) ? ' class="active"' : '') }}>
        <a href="{{ URL::route('organisation_list') }}"><i class="fa fa-users"></i> <span class="nav-label">Communauté</span>
            <span class="fa arrow"></span></a>
        <ul class="nav nav-second-level {{ ((Request::is('user*') || Request::is('organisation*')) ? '' : 'collapse') }}">
            <li{{ Request::is('organisation*') ? ' class="active"' : '' }}>
                <a href="{{ URL::route('organisation_list') }}"><i class="fa fa-building"></i> Sociétés</a>
            </li>
            <li{{ Request::is('user*') ? ' class="active"' : '' }}>
                <a href="{{ URL::route('user_list') }}"><i class="fa fa-user"></i> Membres</a>
            </li>
            {{--<li><a href="{{ URL::route('user_directory') }}">Annuaire</a></li>--}}
        </ul>
    </li>
    <li{{ Request::is('pasttime*') ? ' class="active"' : '' }}>
        <a href="{{ URL::route('pasttime_list') }}">
            <i class="fa fa-clock-o"></i>
            <span class="nav-label">Temps passé</span>
        </a>
    </li>


    <li{{ (Request::is('booking*') ? ' class="active"' : '') }}>
        <a href="{{ URL::route('booking_list') }}"><i class="fa fa-calendar"></i> <span class="nav-label">Réservations</span>
            <span class="fa arrow"></span></a>
        <ul class="nav nav-second-level {{ (Request::is('booking*') ? '' : 'collapse') }}">
            <li{{ Request::is('booking') ? ' class="active"' : '' }}>
                <a href="{{ URL::route('booking') }}"><i class="fa fa-calendar"></i> Calendrier</a>
            </li>
            <li{{ Request::is('booking/list') ? ' class="active"' : '' }}>
                <a href="{{ URL::route('booking_list') }}"><i class="fa fa-calendar-o"></i> Liste</a>
            </li>
            {{--<li><a href="{{ URL::route('user_directory') }}">Annuaire</a></li>--}}
        </ul>
    </li>

    {{--<li{{ Request::is('booking*') ? ' class="active"' : '' }}>--}}
    {{--<a href="{{ URL::route('booking_list') }}"><i class="fa fa-th-large"></i> <span class="nav-label">Réservations</span></a>--}}
    {{--</li>--}}

    <li class="{{ ((Request::is('invoice*') or Request::is('quote*') || Request::is('cashflow*') || Request::is('charge*') || Request::is('subscription*')) ? 'active' : '') }}">
        <a href="{{ URL::route('invoice_list') }}"><i class="fa fa-money"></i> <span class="nav-label">Gestion</span>
            <span class="fa arrow"></span></a>
        <ul class="nav nav-second-level {{ ((Request::is('invoice*') or Request::is('quote*') || Request::is('charge*')) ? '' : 'collapse') }}">
            <li{{ Request::is('subscription*') ? ' class="active"' : '' }}>
                <a href="{{ URL::route('subscription_list') }}">Abonnements</a>
            </li>
            <li{{ Request::is('invoice*') ? ' class="active"' : '' }}>
                <a href="{{ URL::route('invoice_list') }}">Factures</a>
            </li>
            <li{{ Request::is('quote*') ? ' class="active"' : '' }}>
                <a href="{{ URL::route('quote_list', 'all') }}">Devis</a>
            </li>
            <li{{ Request::is('charge*') ? ' class="active"' : '' }}>
                <a href="{{ URL::route('charge_list', 'all') }}">Dépenses</a>
            </li>
            <li{{ Request::is('cashflow*') ? ' class="active"' : '' }}>
                <a href="{{ URL::route('vat_overview', 'all') }}">TVA</a>
            </li>
        </ul>
    </li>



    <li class="{{ Request::is('stats*') ? 'active' : '' }}">
        <a href="{{ URL::route('stats_overview') }}"><i class="fa fa-bar-chart-o"></i> <span class="nav-label">Statistiques</span>
            <span class="fa arrow"></span></a>
        <ul class="nav nav-second-level {{  Request::is('stats*')? '' : 'collapse' }}">
            <li{{ Request::is('stats/overview') ? ' class="active"' : '' }}>
                <a href="{{ URL::route('stats_overview') }}">Vue d'ensemble</a>
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
            <li{{ Request::is('stats/charges') ? ' class="active"' : '' }}>
                <a href="{{ URL::route('stats_charges') }}">Dépenses</a>
            </li>
        </ul>
    </li>

    <li class="{{ ((Request::is('ressource*') or Request::is('countries*') or Request::is('country*') || Request::is('vat*') || Request::is('tag*')) ? 'active' : '') }}">
        <a href="{{ URL::route('dashboard') }}"><i class="fa fa-gear"></i> <span
                    class="nav-label">Configuration</span> <span class="fa arrow"></span></a>
        <ul class="nav nav-second-level {{ ((Request::is('ressource*') || Request::is('country*') || Request::is('vat*') || Request::is('tag*')) ? '' : 'collapse') }}">
            <li{{ Request::is('ressource*') ? ' class="active"' : '' }}><a href="{{ URL::route('ressource_list') }}">Ressources</a>
            </li>
            <li{{ (Request::is('country*') or Request::is('countries*')) ? ' class="active"' : '' }}><a
                        href="{{ URL::route('country_list') }}">Pays</a></li>
            <li{{ Request::is('vat*') ? ' class="active"' : '' }}><a href="{{ URL::route('vat_list') }}">TVA</a></li>
            <li{{ Request::is('tag*') ? ' class="active"' : '' }}><a href="{{ URL::route('tag_list') }}">Tags</a></li>
        </ul>
    </li>
</ul>
