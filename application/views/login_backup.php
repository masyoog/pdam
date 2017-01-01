<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
        <!-- Latest compiled and minified CSS -->
        <!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">-->
        <link rel="stylesheet" href="http://localhost/hris/assets/bootstrap/css/bootstrap.min.css">

        <!-- Optional theme -->
        <!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css">-->
        <link rel="stylesheet" href="http://localhost/hris/assets/bootstrap/css/bootstrap-theme.min.css">

        <!-- Latest compiled and minified JavaScript -->
        <style>
            body { padding-top: 50px; font-size: 12px;}
            html, body, .container{ height: 100%; }
            .container {                
                display: table;
                vertical-align: middle;
            }
            .vertical-center-row {
                display: table-cell;
                vertical-align: middle;
            }

            .navbar-custom {
                background-color:#1F6074;
                color:#ffffff;
                border-radius:0;
            }

            .navbar-custom .navbar-nav > li > a {
                color:#fff;
            }
            .navbar-custom .navbar-nav > .active > a, .navbar-nav > .active > a:hover, .navbar-nav > .active > a:focus {
                color: #ffffff;
                background-color:transparent;
            }
            .navbar-custom {
                color:#eeeeee;
            }

            .navbar-brand { color: #111111;}

            .table { margin-bottom: 0px;}
            .table th { background: #B8D7D8;}
            .table-bordered>thead>tr>th, 
            .table-bordered>tbody>tr>th, 
            .table-bordered>tfoot>tr>th, 
            .table-bordered>thead>tr>td, 
            .table-bordered>tbody>tr>td, 
            .table-bordered>tfoot>tr>td {
                border: 1px solid #B1A2A2;
            }

            .page-header h4 { color: #FC7003; margin: 0; padding: 0;}

            #form-search { margin-bottom: 15px;}


            .form-control { height: 30px;}

            .list-group-item { background: #F5A05D; padding: 8px 10px;}
            .list-group-item.active, .list-group-item.active:hover, .list-group-item.active:focus {background-image: linear-gradient(to bottom,#EBEFF1 0,#FFFFFF 100%); text-shadow: none; border-color:#E8ECF0;}
            .list-group-item.active { color: #333333; font-weight: bold;}

            .form-bu-kanan { margin-top: -30px; float: right;}

            .btn-sm { padding: 2px 4px;}
            .btn-command span { margin-right: 5px;}


            #logo-hris {margin-right: 5px;}
            #ul-parent-side-menu {
                -moz-box-shadow: 1px 2px 2px 1px #E4E4E4;
                -webkit-box-shadow: 1px 2px 2px 1px #E4E4E4;
                box-shadow: 1px 2px 2px 1px #E4E4E4;
            }
            #logo-amg { 
                /*border: 1px solid red; */
                width: 100%; 
                min-height:110px; 
                background: url(assets/img/argo-manunggal.gif) no-repeat; background-size: 550px 108px;
                -moz-box-shadow: 1px 2px 2px 1px #E4E4E4;
                -webkit-box-shadow: 1px 2px 2px 1px #E4E4E4;
                box-shadow: 1px 2px 2px 1px #E4E4E4;
                /*box-shadow: 1px 2px 2px 2px #E4E4E4;*/
            }

            .kotak-bayangan {
                -moz-box-shadow: 1px 2px 2px 1px #E4E4E4;
                -webkit-box-shadow: 1px 2px 2px 1px #E4E4E4;
                box-shadow: 1px 2px 2px 1px #E4E4E4;
            }
            .parent-side-menu{ font-weight: bold;}
            .child-side-menu { border:none; background: transparent; margin-top: 5px; padding: 0 0 0 15px;}
            .child-side-menu li { color: #fff; list-style: disc; border-top: 1px solid #ddd; padding: 5px 0;}

            #login-form { padding: 10px 0 0 0;}
        </style>
    </head>
    <body>
        <nav class="navbar navbar-custom navbar-fixed-top" role="navigation">
            <div class="container-fluid">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#"><span id="logo-hris" class="glyphicon glyphicon-th"></span>HRIS&nbsp;<small>Human Resource Information System</small></a>
                </div>

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">

                </div><!-- /.navbar-collapse -->
            </div><!-- /.container-fluid -->
        </nav>

        <div class="container">
            <div class="row vertical-center-row">
                <div class="col-md-12">
                    <div class="row ">
                        <div class="col-md-4 col-md-offset-4 kotak-bayangan" style="padding-top: 15px; background-color: #eaeaea;" >
                            <form  class="form-horizontal" role="form"  method="post" action="<?php echo base_url() ?>authorization/authorized">
                                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash() ?>" />
                                <h1>
                                    <span class="log-in">Log in</span>
                                    <span class="error-msg"><?php echo $error_msg; ?></span>
                                </h1>
                                <div class="form-group">
                                    <label for="login" class="col-md-3 control-label">Username</label>
                                    <div class="col-md-9">
                                        <input type="text" class="form-control" name="login" id="login" placeholder="Username">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="password" class="col-md-3 control-label">Password</label>
                                    <div class="col-md-9">
                                        <input type="password" class="form-control" name="password" id="password" placeholder="Password">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-md-offset-3 col-md-9">
                                        <button type="submit" name="submit" class="btn btn-sm btn-primary">Login</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>    
        <!--<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js" ></script>-->
    <script src="http://localhost/hris/assets/jquery/jquery.min.js" ></script>
    <!--<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>-->
    <script src="http://localhost/hris/assets/bootstrap/js/bootstrap.min.js"></script>

    <script>
        $(function() {
            $('.child-side-menu').hide();
            $('.parent-side-menu').click(function(event) {
                console.log($(this).children('ul.child-side-menu').toggle());
            });
        });
    </script>    
</body>
</html>
