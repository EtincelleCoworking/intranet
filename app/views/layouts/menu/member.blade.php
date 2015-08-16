<ul class="nav metismenu" id="side-menu">
    <li class="nav-header">
        <div class="dropdown profile-element">
            <span>
                <img alt="image" class="img-circle img-responsive" src="{{Auth::user()->getGravatarUrl(48)}}"/>
            </span>
            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                <span class="clear"> <span class="block m-t-xs">
                        <strong class="font-bold">{{Auth::user()->fullname}}</strong>
                </span>
                <span class="text-muted text-xs block">Membre <b class="caret"></b></span> </span>
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

    <li{{ (Request::is('user*') ||  Request::is('profile*')) ? ' class="active"' : '' }}>
        <a href="{{ URL::route('user_list') }}"><i class="fa fa-user"></i> <span class="nav-label">Membres</span></a>
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

    <li{{ Request::is('pasttime*') ? ' class="active"' : '' }}>
        <a href="{{ URL::route('pasttime_list') }}"><i class="fa fa-clock-o"></i> <span
                    class="nav-label">Temps passé</span></a>
    </li>
    @if (Auth::user()->hasQuotes())
    <li{{ Request::is('quote*') ? ' class="active"' : '' }}>
        <a href="{{ URL::route('quote_list', 'all') }}"><i class="fa fa-file-text"></i> <span class="nav-label">Devis</span></a>
    </li>
    @endif

    {{-- */ $invoiceCount = Auth::user()->getPendingInvoiceCount(); /* --}}

    <li{{ Request::is('invoice*') ? ' class="active"' : '' }}>
        <a href="{{ URL::route('invoice_list') }}">
            <i class="fa fa-money"></i>
            <span class="nav-label">Factures</span>
    @if ($invoiceCount)
            <span class="label label-danger pull-right">{{$invoiceCount}}</span>
    @endif
        </a>
    </li>

</ul>


