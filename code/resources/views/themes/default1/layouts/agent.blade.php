<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Faveo | HELP DESK</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <link href="http://code.ionicframework.com/ionicons/2.0.0/css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <link href="{{asset("plugins/fullcalendar/fullcalendar.min.css")}}" rel="stylesheet" type="text/css" />
        <link href="{{asset("plugins/fullcalendar/fullcalendar.print.css")}}" rel="stylesheet" type="text/css" media='print' />
        <link href="{{asset("dist/css/AdminLTE.min.css")}}" rel="stylesheet" type="text/css" />
        <link href="{{asset("dist/css/skins/_all-skins.min.css")}}" rel="stylesheet" type="text/css" />
        <link href="{{asset("plugins/iCheck/flat/blue.css")}}" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" href="{{asset("dist/css/tabby.css")}}" type="text/css">
        <link href="http://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">
        <link type="text/css" href="http://code.jquery.com/ui/1.9.1/themes/redmond/jquery-ui.css" rel="stylesheet">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        @yield('HeadInclude')
    </head>
    <body class="skin-blue">
        <div class="wrapper" id="RefreshAssign">
            <header class="main-header">
                <a href="../../index2.html" class="logo"><b>Faveo</b> HELP DESK</a>
                <nav class="navbar navbar-static-top" role="navigation">
                    <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <div class="collapse navbar-collapse" id="navbar-collapse">
                        <ul class="tabs tabs-horizontal nav navbar-nav">
                            <li @yield('Dashboard')><a data-target="#tabA" href="#">Dashboard</a></li>
                            <li @yield('Users')><a data-target="#tabB" href="#">Users</a></li>
                            <li @yield('Tickets')><a data-target="#tabC" href="#">Tickets</a></li>
                        </ul>
                        <ul class="nav navbar-nav navbar-right">
                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <img src="dist/img/user2-160x160.jpg" class="user-image" alt="User Image"/>
                                    <span class="hidden-xs">Alexander Pierce</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li class="user-header">
                                        <img src="dist/img/user2-160x160.jpg" class="img-circle" alt="User Image" />
                                        <p>
                                            Alexander Pierce - Web Developer
                                            <small>Member since Nov. 2012</small>
                                        </p>
                                    </li>
                                    <li class="user-body">
                                        <div class="col-xs-4 text-center">
                                            <a href="#">Followers</a>
                                        </div>
                                        <div class="col-xs-4 text-center">
                                            <a href="#">Sales</a>
                                        </div>
                                        <div class="col-xs-4 text-center">
                                            <a href="#">Friends</a>
                                        </div>
                                    </li>
                                    <li class="user-footer">
                                        <div class="pull-left">
                                            <a href="#" class="btn btn-default btn-flat">Profile</a>
                                        </div>
                                        <div class="pull-right">
                                            <a href="{{ url('/auth/logout') }}" class="btn btn-default btn-flat">Sign out</a>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>
            <aside class="main-sidebar">
                <section class="sidebar">
                    <form action="#" method="get" class="sidebar-form">
                        <div class="input-group">
                            <input type="text" name="q" class="form-control" placeholder="Search..."/>
                            <span class="input-group-btn">
                                <button type='submit' name='seach' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
                            </span>
                        </div>
                    </form>
                    <ul class="sidebar-menu">
                        @yield('sidebar')
                        <li class="header">TICKETS</li>
                        <li>
                            <a href="../widgets.html">
                                <i class="fa fa-envelope"></i> <span>Inbox</span> <small class="label pull-right bg-green">5</small>
                            </a>
                        </li>
                        <li @yield('myticket')>
                            <a href="{{url('ticket/myticket')}}">
                                <i class="fa fa-user"></i> <span>My Tickets</span>
<?php $myticket = App\Model\Ticket\Tickets::where('user_id', Auth::user()->id)->get();?>
                                <small class="label pull-right bg-green">{{count($myticket) }}</small>
                            </a>
                        </li>
                        <li @yield('unassigned')>
                            <a href="{{url('unassigned')}}">
                                <i class="fa fa-th"></i> <span>Unassigned</span>
<?php $unassigned = App\Model\Ticket\Tickets::where('assigned_to', '0')->get();?>
                                 <small class="label pull-right bg-green">{{count($unassigned)}}</small>
                            </a>
                        </li>
                        <li @yield('trash')>
                            <a href="{{url('trash')}}">
                                <i class="fa fa-trash-o"></i> <span>Trash</span>
<?php $deleted = App\Model\Ticket\Tickets::where('status', '5')->get();?>
                                 <small class="label pull-right bg-green">{{count($deleted)}}</small>
                            </a>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-folder-open"></i> <span>General</span> <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href=""><i class="fa fa-circle-o"></i>Open<small class="label pull-right bg-green">4</small></a></li>
                                <li><a href=""><i class="fa fa-circle-o"></i>Inprogress<small class="label pull-right bg-green">3</small></a></li>
                                <li><a href=""><i class="fa fa-circle-o"></i>Closed<small class="label pull-right bg-green">55</small></a></li>
                            </ul>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-folder-open"></i> <span>Support</span> <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href=""><i class="fa fa-circle-o"></i>Open<small class="label pull-right bg-green">1</small></a></li>
                                <li><a href=""><i class="fa fa-circle-o"></i>Inprogress<small class="label pull-right bg-green">6</small></a></li>
                                <li><a href=""><i class="fa fa-circle-o"></i>Closed<small class="label pull-right bg-green">88</small></a></li>
                            </ul>
                        </li>
                        <li class="header">LABELS</li>
                        <li><a href="#"><i class="fa fa-circle-o text-danger"></i> Important</a></li>
                        <li><a href="#"><i class="fa fa-circle-o text-warning"></i> Warning</a></li>
                        <li><a href="#"><i class="fa fa-circle-o text-info"></i> Information</a></li>
                    </ul>
                </section>
            </aside>
            <div class="content-wrapper">
                <div class="tab-content" style="background-color: white; border-top:1px solid #F0F0F0;">
                    <div class="collapse navbar-collapse" id="navbar-collapse">
                        <div class="tabs-content">
                            <div class="tabs-pane @yield('dashboard-bar')"  id="tabA">
                                <ul class="nav navbar-nav">
                                    <li id="bar" @yield('dashboard') ><a href="#">Dashboard</a></li>
                                    <li id="bar" @yield('profile') ><a href="#">Profile</a></li>
                                </ul>
                            </div>
                            <div class="tabs-pane @yield('user-bar')" id="tabB">
                                <ul class="nav navbar-nav">
                                    <li id="bar" @yield('user')><a href="{{ url('user') }}" >User Directory</a></li></a></li>
                                    <li id="bar" @yield('organizations')><a href="{{ url('organizations') }}" >Organizations</a></li></a></li>
                                </ul>
                            </div>
                            <div class="tabs-pane @yield('ticket-bar')" id="tabC">
                                <ul class="nav navbar-nav">
                                    <li id="bar" @yield('open')><a href="{{ url('/ticket/open') }}" >Open</a></li>
                                    <li id="bar" @yield('answered')><a href="{{ url('/ticket/answered') }}" >Answered</a></li>
                                    <li id="bar" @yield('myticket')><a href="{{ url('/ticket/myticket') }}" >My Ticket</a></li>
                                    <li id="bar" @yield('ticket')><a href="{{ url('ticket') }}" >Ticket</a></li>
                                    <li id="bar" @yield('overdue')><a href="{{ url('/ticket/overdue') }}" >Overdue</a></li>
                                    <li id="bar" @yield('closed')><a href="{{ url('/ticket/closed') }}" >Closed</a></li>
                                    <li id="bar" @yield('newticket')><a href="{{ url('/newticket') }}" >New Ticket</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <section class="content-header">
                    @yield('PageHeader')
                    @yield('breadcrumbs')
                </section>
                <section class="content">
                    @yield('content')
                </section>
            </div>
            <footer class="main-footer">
                <div class="pull-right hidden-xs">
                    <b>Version</b> 0.1
                </div>
                <strong>Copyright &copy; 2014-2015 <a href="http://www.ladybirdweb.com">Ladybird Web Solution</a>.</strong> All rights reserved.
            </footer>
        </div>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="{{asset("plugins/slimScroll/jquery.slimscroll.min.js")}}" type="text/javascript"></script>
        <script src='{{asset("plugins/fastclick/fastclick.min.js")}}'></script>
        <script src="{{asset("dist/js/app.min.js")}}" type="text/javascript"></script>
        <script src="{{asset("dist/js/demo.js")}}" type="text/javascript"></script>
        <script src="{{asset("plugins/iCheck/icheck.min.js")}}" type="text/javascript"></script>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>
        <script type="text/javascript" src="js/jquery.maskedinput.min.js"></script>
        <script>
$(function() {
    //Enable iCheck plugin for checkboxes
    //iCheck for checkbox and radio inputs
    $('input[type="checkbox"]').iCheck({
        checkboxClass: 'icheckbox_flat-blue',
        radioClass: 'iradio_flat-blue'
    });
    //Enable check and uncheck all functionality
    $(".checkbox-toggle").click(function() {
        var clicks = $(this).data('clicks');
        if (clicks) {
            //Uncheck all checkboxes
            $("input[type='checkbox']", ".mailbox-messages").iCheck("uncheck");
        } else {
            //Check all checkboxes
            $("input[type='checkbox']", ".mailbox-messages").iCheck("check");
        }
        $(this).data("clicks", !clicks);
    });
    //Handle starring for glyphicon and font awesome
    $(".mailbox-star").click(function(e) {
        e.preventDefault();
        //detect type
        var $this = $(this).find("a > i");
        var glyph = $this.hasClass("glyphicon");
        var fa = $this.hasClass("fa");
        //Switch states
        if (glyph) {
            $this.toggleClass("glyphicon-star");
            $this.toggleClass("glyphicon-star-empty");
        }
        if (fa) {
            $this.toggleClass("fa-star");
            $this.toggleClass("fa-star-o");
        }
    });
});
        </script>
        <script src="{{asset("dist/js/tabby.js")}}"></script>
        @yield('FooterInclude')
    </body>
</html>
