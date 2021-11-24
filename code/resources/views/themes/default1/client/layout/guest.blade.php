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
         @yield('HeadInclude')
  </head>
  <body class="skin-blue layout-boxed">
    <div class="wrapper">
      <header class="main-header">
       <?php $company = App\Model\Settings\Company::where('id','=','1')->first();?>
                <img src="{{asset('dist')}}{{'/'}}{{$company->logo}}" class="logo" alt="User Image" />
        <nav class="navbar navbar-static-top" role="navigation">
          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
          </a>
          <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
              @if(Auth::user())
              <li class="dropdown messages-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <i class="fa fa-envelope-o"></i>
                  <span class="label label-success">4</span>
                </a>
                <ul class="dropdown-menu">
                  <li class="header">You have 4 messages</li>
                  <li>
                    <ul class="menu">
                      <li>
                        <a href="#">
                          <div class="pull-left">
                            <img src="{{asset('dist/img')}}{{'/'}}{{Auth::user()->profile_pic}}" class="img-circle" alt="User Image"/>
                          </div>
                          <h4>
                            Support Team
                            <small><i class="fa fa-clock-o"></i> 5 mins</small>
                          </h4>
                          <p>Why not buy a new awesome theme?</p>
                        </a>
                      </li>
                    </ul>
                  </li>
                  <li class="footer"><a href="#">See All Messages</a></li>
                </ul>
              </li>
              <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                  <img src="{{asset('dist/img')}}{{'/'}}{{Auth::user()->profile_pic}}"class="user-image" alt="User Image"/>
                  <span class="hidden-xs">{{Auth::user()->name}}</span>
                </a>
                <ul class="dropdown-menu">
                  <li class="user-header">
                    <img src="{{asset('dist/img')}}{{'/'}}{{Auth::user()->profile_pic}}" class="img-circle" alt="User Image" />
                    <p>
                   {{Auth::user()->name}} - {{Auth::user()->role}}
                      <small></small>
                    </p>
                  </li>
                  <li class="user-footer">
                    <div class="pull-left">
                      <a href="{{url('agent-profile/'.Auth::user()->id)}}" class="btn btn-default btn-flat">Profile</a>
                    </div>
                    <div class="pull-right">
                      <a href="{{url('auth/logout')}}" class="btn btn-default btn-flat">Sign out</a>
                    </div>
                  </li>
                </ul>
              </li>
              @else
                <li>
                  <a href="{{url('auth/login')}}" class="logo"><span>Sign In</span></a>
                </li>
                <li>
                  <a href="{{url('auth/register')}}" class="logo"><span>Register</span></a>
                </li>
              @endif
            </ul>
          </div>
        </nav>
      </header>
      <aside class="main-sidebar">
        <section class="sidebar">
          @if(Auth::user())
          <div class="user-panel">
            <div class="pull-left image">
              <img src="{{asset('dist/img')}}{{'/'}}{{Auth::user()->profile_pic}}" class="img-circle" alt="User Image" />
            </div>
            <div class="pull-left info">
              <p>{{Auth::user()->name}}</p>
              @if(Auth::user()->active==1)
                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
              @else
                <a href="#"><i class="fa fa-circle"></i> Offline</a>
              @endif
            </div>
          </div>
          <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
              <input type="text" name="q" class="form-control" placeholder="Search..."/>
              <span class="input-group-btn">
                <button type='submit' name='seach' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
              </span>
            </div>
          </form>
          <ul class="sidebar-menu">
            <li class="header">MAIN NAVIGATION</li>
            <li class="treeview">
              <a href="#">
                <i class="fa fa-dashboard"></i> <span>Dashboard</span> <i class="fa fa-angle-left pull-right"></i>
              </a>
              <ul class="treeview-menu">
                <li><a href="../../index.html"><i class="fa fa-circle-o"></i> Dashboard v1</a></li>
                <li><a href="../../index2.html"><i class="fa fa-circle-o"></i> Dashboard v2</a></li>
              </ul>
            </li>
            <li>
              <a href="{{url('myticket')}}">
                <i class="fa fa-th"></i> <span>MyTickets</span> 
              </a>
            </li>
            <li>
              <a href="{{url('getform')}}">
                <i class="fa fa-th"></i> <span>Submit a Tickets</span> 
              </a>
            </li>
            </ul>
        </section>
        @else
        <ul class="sidebar-menu">
            <li>
              <a href="{{url('getform')}}">
                <i class="fa fa-envelope"></i> <span>Open A New Ticket</span>
              </a>
            </li>
        </ul>
        <ul class="{{url('newticket')}}">
            <li>
              <a href="#">
                <i class="fa fa-th"></i> <span>Check your Ticket</span>
              </a>
            </li>
        </ul>
        @endif
      </aside>
      <div class="content-wrapper">
        <section class="content-header">
          @yield('header')
        </section>
        <section class="content">
          @if(Auth::user())
            @yield('content1')
          @else
            @yield('content')
          @endif
        </section>
      </div>
<?php $sys = App\Model\Settings\System::where('id','=','1')->first();?>
      <footer class="main-footer">
        <div class="pull-right hidden-xs">
          <b>Version</b> 2.0
        </div>
        <strong>Copyright &copy; 2014-2015 <a href="{{$sys->url}}">{{$sys->name}}</a>.</strong> All rights reserved.
      </footer>
    </div>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="{{asset("plugins/slimScroll/jquery.slimscroll.min.js")}}" type="text/javascript"></script>
        <script src='{{asset("plugins/fastclick/fastclick.min.js")}}'></script>
        <script src="{{asset("dist/js/app.min.js")}}" type="text/javascript"></script>
        <script src="{{asset("dist/js/demo.js")}}" type="text/javascript"></script>
        <script src="{{asset("plugins/iCheck/icheck.min.js")}}" type="text/javascript"></script>
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
