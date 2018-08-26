<ul class="nav metismenu" id="side-menu">
    <li class="nav-header">
        <div class="dropdown profile-element">
            <span>
                <a href="{{URL::Route('user_profile', Auth::user()->id)}}"><img alt="{{Auth::user()->fullname}}"
                                                                                class="img-circle img-responsive"
                                                                                src="{{Auth::user()->getAvatarUrl(48)}}"/></a>
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
        <a href="{{ URL::route('dashboard') }}"><i class="fa fa-th-large"></i> <span
                    class="nav-label">Tableau de bord</span></a>
    </li>


    <li{{ ((Request::is('user*') || Request::is('organisation*')) ? ' class="active"' : '') }}>
        <a href="{{ URL::route('organisation_list') }}"><i class="fa fa-users"></i> <span
                    class="nav-label">Communauté</span>
            <span class="fa arrow"></span></a>
        <ul class="nav nav-second-level {{ ((Request::is('user*') || Request::is('organisation*')) ? '' : 'collapse') }}">
            <li{{ (Request::is('organisation*') && !Request::is('organisation_postbox')) ? ' class="active"' : '' }}>
                <a href="{{ URL::route('organisation_list') }}"><i class="fa fa-building"></i> Sociétés</a>
            </li>
            <li{{ (Request::is('user*') && !Request::is('user/list') && !Request::is('user/birthday'))? ' class="active"' : '' }}>
                <a href="{{ URL::route('members') }}"><i class="fa fa-users"></i> Membres</a>
            </li>
            <li{{ Request::is('user/list') ? ' class="active"' : '' }}>
                <a href="{{ URL::route('user_list') }}"><i class="fa fa-user"></i> Utilisateurs</a>
            </li>
            <li{{ Request::is('user/birthday') ? ' class="active"' : '' }}>
                <a href="{{ URL::route('user_birthday') }}"><i class="fa fa-birthday-cake"></i> Anniversaires</a>
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
    {{--
    <li{{ Request::is('subscription') ? ' class="active"' : '' }}>
        <a href="{{ URL::route('subscription_manage') }}"><i class="fa fa-ticket"></i> <span
                    class="nav-label">Abonnement</span></a>
    </li>
    --}}
    <li{{ (Request::is('booking*') ? ' class="active"' : '') }}>
        <a href="{{ URL::route('booking_list') }}"><i class="fa fa-calendar"></i> <span
                    class="nav-label">Réservations</span>
            <span class="fa arrow"></span></a>
        <ul class="nav nav-second-level {{ (Request::is('booking*') ? '' : 'collapse') }}">
            <li{{ Request::is('booking') ? ' class="active"' : '' }}>
                <a href="{{ URL::route('booking') }}"><i class="fa fa-calendar"></i> Calendrier</a>
            </li>
            <li{{ Request::is('booking/list') ? ' class="active"' : '' }}>
                <a href="{{ URL::route('booking_list') }}"><i class="fa fa-calendar-o"></i> Liste</a>
            </li>
            <li{{ Request::is('booking/dailyPdf') ? ' class="active"' : '' }}>
                <a href="{{ URL::route('booking_daily_pdf', array('location' => Auth::user()->location->slug, 'day' => date('Y-m-d'))) }}"
                   target="_blank"><i class="fa fa-file-pdf-o"></i> PDF</a>
            </li>
        </ul>
    </li>

    <li{{(Request::is('postbox*')) ? ' class="active"' : '' }}>
        <a href="{{ URL::route('postbox') }}"><i class="fa fa-envelope"></i> Domiciliation</a>
    </li>

    <li{{ (Request::is('planning*') ? ' class="active"' : '') }}>
        <a href="{{ URL::route('planning') }}"><i class="fa fa-calendar-o"></i> <span class="nav-label">Planning</span></a>
    </li>
    {{--

<li{{ (Request::is('issues*') ? ' class="active"' : '') }}>
<a href="{{ URL::route('issues') }}"><i class="fa fa-tasks"></i> <span class="nav-label">Tâches</span></a>
</li>
--}}
{{--<li{{ Request::is('booking*') ? ' class="active"' : '' }}>--}}
    {{--<a href="{{ URL::route('booking_list') }}"><i class="fa fa-th-large"></i> <span class="nav-label">Réservations</span></a>--}}
    {{--</li>--}}

    <li class="{{ ((Request::is('invoice*') or Request::is('quote*') || Request::is('cashflow*') || Request::is('charge*') || Request::is('subscriptions*') || Request::is('device*')) ? 'active' : '') }}">
        <a href="{{ URL::route('invoice_list') }}"><i class="fa fa-money"></i> <span class="nav-label">Gestion</span>
            <span class="fa arrow"></span></a>
        <ul class="nav nav-second-level {{ ((Request::is('invoice*') or Request::is('quote*') || Request::is('charge*')) ? '' : 'collapse') }}">
            <li{{ Request::is('subscription*') ? ' class="active"' : '' }}>
                <a href="{{ URL::route('subscription_list') }}">Abonnements</a>
            </li>
            <li{{ (Request::is('invoice*') && !Request::is('invoice_unpaid') && !Request::is('invoice_invoicing')) ? ' class="active"' : '' }}>
                <a href="{{ URL::route('invoice_list') }}">Factures</a>
            </li>
            <li{{ (Request::is('invoice_invoicing')) ? ' class="active"' : '' }}>
                <a href="{{ URL::route('invoice_invoicing') }}">Facturation</a>
            </li>
            <li{{ (Request::is('invoice_coworking_pending')) ? ' class="active"' : '' }}>
                <a href="{{ URL::route('invoice_coworking_pending') }}">F - Coworking</a>
            </li>
            <li{{ Request::is('invoice_unpaid') ? ' class="active"' : '' }}>
                <a href="{{ URL::route('invoice_unpaid') }}">Impayés</a>
            </li>
            <li{{ Request::is('quote*') ? ' class="active"' : '' }}>
                <a href="{{ URL::route('quote_list', 'all') }}">Devis</a>
            </li>
            <li{{ Request::is('charge*') ? ' class="active"' : '' }}>
                <a href="{{ URL::route('charge_list', 'all') }}">Dépenses</a>
            </li>
            <li{{ Request::is('vat*') ? ' class="active"' : '' }}>
                <a href="{{ URL::route('vat_overview', 'all') }}">TVA</a>
            </li>
            <li{{ Request::is('cashflow*') ? ' class="active"' : '' }}>
                <a href="{{ URL::route('cashflow', 'all') }}">Trésorerie</a>
            </li>
            <li{{ Request::is('device*') ? ' class="active"' : '' }}>
                <a href="{{ URL::route('device_list', 'all') }}">Périphériques</a>
            </li>
        </ul>
    </li>


    @include('layouts.menu._stats')

    <li class="{{ ((Request::is('ressource*') or Request::is('countries*') or Request::is('country*') || Request::is('vat*') || Request::is('tag*')) ? 'active' : '') }}">
        <a href="{{ URL::route('dashboard') }}"><i class="fa fa-gear"></i> <span
                    class="nav-label">Configuration</span> <span class="fa arrow"></span></a>
        <ul class="nav nav-second-level {{ ((Request::is('ressource*') || Request::is('country*') || Request::is('vat*') || Request::is('tag*')) ? '' : 'collapse') }}">
            <li{{ Request::is('ressource*') ? ' class="active"' : '' }}><a href="{{ URL::route('ressource_list') }}">Ressources</a>
            <li{{ Request::is('location*') ? ' class="active"' : '' }}><a
                        href="{{ URL::route('location_list') }}">Sites</a>
            </li>
            <li{{ (Request::is('country*') or Request::is('countries*')) ? ' class="active"' : '' }}><a
                        href="{{ URL::route('country_list') }}">Pays</a></li>
            <li{{ Request::is('vat*') ? ' class="active"' : '' }}><a href="{{ URL::route('vat_list') }}">TVA</a></li>
            <li{{ Request::is('tag*') ? ' class="active"' : '' }}><a href="{{ URL::route('tag_list') }}">Tags</a></li>
        </ul>
    </li>
</ul>
