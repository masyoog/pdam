
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
        <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
        <title>LOGIN</title>        
        <meta name="author" content="Codrops" />
<!--        <link rel="shortcut icon" href="../favicon.ico"> -->
        <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>themes/css/style.css" />
        
        <script src="<?php echo base_url()?>assets/jquery-ui/js/jquery-1.9.1.js"></script>
        <script src="<?php echo base_url();?>assets/js/modernizr.custom.63321.js"></script>
        <!--[if lte IE 7]><style>.main{display:none;} .support-note .note-ie{display:block;}</style><![endif]-->
        <style>
                body {
/*                        background: #e1c192 url(images/wood_pattern.jpg);*/
/*                        background: #414e5b;*/
/*                        background: #94987e;*/
                }
                
                #top1 { background: #94987e/*ccd6dc*/; width: 100%; height: 50px;}
                #top1_logo { background: transparent; padding: 5px; width: 40px; height: 40px; margin-left: 20px; float: left;}
                #top1_caption { color: #e3560a; padding-top: 12px; width: 300px;}

                #top2 { background: #cecca8/*#1e506f*/; width: 100%; height: 30px;}
                #top3 { background: #94987e; width: 100%; height: 10px;}
                #top4 { background: #414e5b; width: 100%; height: 20px;}
                .error-msg {float: right; font-size: 10px; color: #E13300; }
        </style>
    </head>
    <body>
        <div id="top4"></div>
        <div id="top2"></div>

        <div id="top3"></div>
        
        <div class="container">
	    <section class="main" >
                    <form class="form-2" method="post" action="<?php echo base_url()?>authorization/authorized">
                            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash()?>" />
                            <h1>
                                <span class="log-in">Log in</span>
                                <span class="error-msg"><?php echo $error_msg;?></span>
                            </h1>

                            <p class="float">
                                    <label for="login"><i class="icon-user"></i>Username</label>
                                    <input type="text" name="login" placeholder="Username or email">
                            </p>
                            <p class="float">
                                    <label for="password"><i class="icon-lock"></i>Password</label>
                                    <input type="password" name="password" placeholder="Password" class="showpassword">
                            </p>
                            <p class="clearfix" > 
                                    <input type="submit" name="submit" value="Log in">
                            </p>
                    </form>​​
            </section>
        </div>
		<!-- jQuery if needed -->
<!--        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>-->
		<script type="text/javascript">
			$(function(){
			    $(".showpassword").each(function(index,input) {
			        var $input = $(input);
			        $("<p class='opt'/>").append(
			            $("<input type='checkbox' class='showpasswordcheckbox' id='showPassword' />").click(function() {
			                var change = $(this).is(":checked") ? "text" : "password";
			                var rep = $("<input placeholder='Password' type='" + change + "' />")
			                    .attr("id", $input.attr("id"))
			                    .attr("name", $input.attr("name"))
			                    .attr('class', $input.attr('class'))
			                    .val($input.val())
			                    .insertBefore($input);
			                $input.remove();
			                $input = rep;
			             })
			        ).append($("<label for='showPassword'/>").text("Show password")).insertAfter($input.parent());
			    });

			    $('#showPassword').click(function(){
					if($("#showPassword").is(":checked")) {
						$('.icon-lock').addClass('icon-unlock');
						$('.icon-unlock').removeClass('icon-lock');    
					} else {
						$('.icon-unlock').addClass('icon-lock');
						$('.icon-lock').removeClass('icon-unlock');
					}
			    });
			});
		</script>
    </body>
</html>
