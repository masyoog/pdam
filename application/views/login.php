<!DOCTYPE html>
<html class="bg-black">
    <head>
        <meta charset="UTF-8">
        <title>PDAM System</title>
        <link rel="shortcut icon" href="<?php echo base_url() ?>/favicon.ico" type="image/x-icon">
        <link rel="icon" href="<?php echo base_url() ?>/favicon.ico" type="image/x-icon">
        <!--<link rel="icon" type="image/png" href="favicon.ico">-->
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <!-- bootstrap 3.0.2 -->
        <link href="<?php echo base_url('assets') ?>/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <!-- font Awesome -->
        <link href="<?php echo base_url('assets') ?>/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Theme style -->
        <link href="<?php echo base_url('assets') ?>/css/AdminLTE.css" rel="stylesheet" type="text/css" />

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
    </head>
    <body class="bg-black">

        <div class="form-box" id="login-box">
            <div class="header">Sign In</div>
            <form method="post" action="<?php echo base_url() ?>authorization/authorized">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash() ?>" />  
                <div class="body bg-gray">
                    <?php if ($error_msg != "") { ?>
                        <div class="form-group">
                            <div id="divErrorMsg" class="alert alert-danger alert-dismissable">
                                <i class="fa fa-ban"></i>
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <b>Alert!</b> <?php echo $error_msg; ?>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <input type="text" name="login" class="form-control" placeholder="User ID"/>
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" class="form-control" placeholder="Password"/>
                    </div>          
                    <!--                    <div class="form-group">
                                            <input type="checkbox" name="remember_me"/> Remember me
                                        </div>-->
                </div>
                <div class="footer">                                                               
                    <button type="submit" name="submit" class="btn bg-olive btn-block">Login</button>  

<!--<p><a href="#">I forgot my password</a></p>-->

                    <!--<a href="register.html" class="text-center">Register a new membership</a>-->
                </div>
            </form>

            <!--            <div class="margin text-center">
                            <span>Sign in using social networks</span>
                            <br/>
                            <button class="btn bg-light-blue btn-circle"><i class="fa fa-facebook"></i></button>
                            <button class="btn bg-aqua btn-circle"><i class="fa fa-twitter"></i></button>
                            <button class="btn bg-red btn-circle"><i class="fa fa-google-plus"></i></button>
            
                        </div>-->
        </div>


        <!-- jQuery 2.0.2 -->
        <script src="<?php echo base_url('assets') ?>/jquery/jquery.min.js"></script>
        <!-- Bootstrap -->
        <script src="<?php echo base_url('assets') ?>/js/bootstrap.min.js" type="text/javascript"></script>        

    </body>
</html>