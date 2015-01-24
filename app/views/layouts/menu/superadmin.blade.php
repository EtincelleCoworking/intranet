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
                                &nbsp;&nbsp;Annuaire
                            </a>
                        </li>
                        <li>
                            <a href="{{ URL::route('user_list') }}">
                                <i class="fa fa-list"></i>
                                &nbsp;&nbsp;Utilisateurs
                            </a>
                        </li>
                        <li>
                            <a href="{{ URL::route('organisation_list') }}">
                                <i class="fa fa-list"></i>
                                &nbsp;&nbsp;Organisations
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="dropdown {{ ((Request::is('invoice*') or Request::is('quote*')) ? 'active' : '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown">
                        <i class="fa fa-money"></i>
                        Devis &amp; Factures
                        <i class="mainnav-caret"></i>
                    </a>

                    <ul class="dropdown-menu" role="menu">
                        <li>
                            <a href="{{ URL::route('invoice_list') }}">
                                <i class="fa fa-list"></i>
                                &nbsp;&nbsp;Factures
                            </a>
                        </li>
                        <li>
                            <a href="{{ URL::route('quote_list') }}">
                                <i class="fa fa-list"></i>
                                &nbsp;&nbsp;Devis
                            </a>
                        </li>

                    </ul>
                </li>

                <li class="dropdown {{ ((Request::is('ressource*') || Request::is('country*') || Request::is('vat*')) ? 'active' : '') }}">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown">
                        <i class="fa fa-cogs"></i>
                        Configuration
                        <i class="mainnav-caret"></i>
                    </a>

                    <ul class="dropdown-menu" role="menu">
                        <li>
                            <a tabindex="-1" href="{{ URL::route('ressource_list') }}">
                                <i class="fa fa-barcode"></i>
                                &nbsp;&nbsp;Ressources
                            </a>

                        </li>
                       <li>
                            <a tabindex="-1" href="{{ URL::route('country_list') }}">
                                <i class="fa fa-flag"></i>
                                &nbsp;&nbsp;Pays
                            </a>

                        </li>
                       <li >
                            <a tabindex="-1" href="{{ URL::route('vat_list') }}">
                                <i class="fa fa-gavel"></i>
                                &nbsp;&nbsp;TVA
                            </a>

                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>
</div>