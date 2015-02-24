<div class="mainnav">
    <div class="container">
        <a class="mainnav-toggle" data-toggle="collapse" data-target=".mainnav-collapse">
            <span class="sr-only">Toggle navigation</span>
            <i class="fa fa-bars"></i>
        </a>

        <nav class="collapse mainnav-collapse" role="navigation">
            <!--
            <form class="mainnav-form pull-right" role="search">
                <input type="text" class="form-control input-md mainnav-search-query" placeholder="Search">
                <button class="btn btn-sm mainnav-form-btn"><i class="fa fa-search"></i></button>
            </form>
            -->

            <ul class="mainnav-menu">
                <li class="{{ ((Request::is('/')) ? 'active' : '') }}">
                    <a href="{{ URL::route('dashboard') }}">
                        Dashboard
                    </a>
                </li>

                <li class="dropdown {{ ((Request::is('user*') || Request::is('organisation*')) ? 'active' : '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown">
                        <i class="fa fa-users"></i>
                        Utilisateurs
                        <i class="mainnav-caret"></i>
                    </a>

                    <ul class="dropdown-menu" role="menu">
                        <li>
                            <a href="{{ URL::route('user_directory') }}">
                                <i class="fa fa-list"></i>
                                &nbsp;&nbsp;Annuaire des utilisateurs
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="dropdown {{ ((Request::is('invoice*')) ? 'active' : '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown">
                        <i class="fa fa-money"></i>
                        Comptabilité
                        <i class="mainnav-caret"></i>
                    </a>

                    <ul class="dropdown-menu" role="menu">
                        <li>
                            <a href="{{ URL::route('quote_list') }}">
                                <i class="fa fa-list"></i>
                                &nbsp;&nbsp;Liste de mes devis
                            </a>
                        </li>
                        <li>
                            <a href="{{ URL::route('invoice_list') }}">
                                <i class="fa fa-list"></i>
                                &nbsp;&nbsp;Liste de mes factures
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="{{ ((Request::is('pasttime*')) ? 'active' : '') }}">
                    <a href="{{ URL::route('pasttime_list') }}">
                        <i class="fa fa-clock-o"></i>
                        Mon temps passé
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>