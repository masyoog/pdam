<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>HRIS | Human Resources Information System</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <link rel="icon" type="image/png" href="<?php echo base_url() ?>/favicon.ico">
        <!-- bootstrap 3.0.2 -->
        <link href="<?php echo base_url('assets') ?>/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <!-- font Awesome -->
        <link href="<?php echo base_url('assets') ?>/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Ionicons -->
        <link href="<?php echo base_url('assets') ?>/css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <!-- daterange picker -->
        <link href="<?php echo base_url('assets') ?>/css/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
        <!-- date picker -->
        <link href="<?php echo base_url('assets') ?>/css/datepicker/datepicker3.css" rel="stylesheet" type="text/css" />

        <!-- DATA TABLES -->
        <link href="<?php echo base_url('assets') ?>/css/datatables/dataTables.bootstrap.css" rel="stylesheet" type="text/css" />
        <!-- selectize -->
        <link href="<?php echo base_url('assets') ?>/selectize/css/selectize.bootstrap3.css" rel="stylesheet" type="text/css" />
        <!-- Theme style -->
        <link href="<?php echo base_url('assets') ?>/css/AdminLTE.css" rel="stylesheet" type="text/css" />

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->

        <style>
            body { font-size: 12px;}
            .content{ padding: 15px 15px; padding-bottom: 0;}
            .btn-grid { min-width: 15px; font-size: 14px; margin-left: 5px;}
            .header .logo{
                background: url(<?php echo base_url() ?>assets/img/logo-HRIS.jpg) no-repeat;
                background-size: cover;
            }
            #tbl-grid th:first-child,#tbl-grid td:first-child{width:33px;} 
            #tbl-grid th:last-child, #tbl-grid td:last-child {max-width: 75px;}
            .right-side > .content-header {                
                padding: 5px 15px 5px 20px;
            }
            .form-group {
                margin-left: 5px;
            }
            /*            .input-group {
                            display: inline-table;
                            vertical-align: middle;
            
                            .input-group-addon,
                            .input-group-btn,
                            .form-control {
                                width: auto !important;
                            }
                        }*/

            /***********tbox style*********/
            .tbox {position:absolute; display:none; padding:14px 17px; z-index:2002}
            .tinner {padding:15px; -moz-border-radius:5px; border-radius:5px; background:#fff url(<?php echo base_url('assets/tinybox') ?>/preload.gif) no-repeat 50% 50%; border-right:1px solid #333; border-bottom:1px solid #333}
            .tmask {position:absolute; display:none; top:0px; left:0px; height:100%; width:100%; background:#000; z-index:2000}
            .tclose {position:absolute; top:0px; right:0px; width:32px; height:32px; cursor:pointer; background:url(<?php echo base_url('assets/tinybox') ?>/close.png) no-repeat}
            .tclose:hover {opacity: 0.7}

            .user-info { margin: 10px auto; width: 400px; text-align: center; color: #999; }
            .user-info a { color: #999; margin: 0 10px; }
            .user-info a.logout { color: #f00; }
            .user-info a.logout:hover { color: #f00; text-decoration: underline; }

            iframe { margin: -16px -18px -15px -15px; padding: 0; border: 0px solid red; }
        </style>
    </head>
    <body class="skin-blue">
<?php echo isset($pages) ? $pages : ""; ?>

        <!-- jQuery 2.0.2 -->
        <!--<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>-->
        <script src="<?php echo base_url('assets') ?>/jquery/jquery.min.js"></script>
        <!-- Bootstrap -->
        <script src="<?php echo base_url('assets') ?>/js/bootstrap.min.js" type="text/javascript"></script>
        <!-- InputMask -->
<!--        <script src="<?php echo base_url('assets') ?>/js/plugins/input-mask/jquery.inputmask.js" type="text/javascript"></script>
        <script src="<?php echo base_url('assets') ?>/js/plugins/input-mask/jquery.inputmask.date.extensions.js" type="text/javascript"></script>
        <script src="<?php echo base_url('assets') ?>/js/plugins/input-mask/jquery.inputmask.extensions.js" type="text/javascript"></script>-->
        <!-- date-range-picker -->
        <script src="<?php echo base_url('assets') ?>/js/plugins/daterangepicker/daterangepicker.js" type="text/javascript"></script>
        <!-- date-picker -->
        <script src="<?php echo base_url('assets') ?>/js/plugins/datepicker/bootstrap-datepicker.js" type="text/javascript"></script>
        <!-- DATA TABES SCRIPT -->
        <script src="<?php echo base_url('assets') ?>/js/plugins/datatables/jquery.dataTables.js" type="text/javascript"></script>
        <script src="<?php echo base_url('assets') ?>/js/plugins/datatables/dataTables.bootstrap.js" type="text/javascript"></script>

        <!--selective select autocomplete-->
        <script src="<?php echo base_url('assets') ?>/selectize/js/standalone/selectize.min.js" type="text/javascript"></script>

        <!--tinybox-->
        <script src="<?php echo base_url('assets') ?>/tinybox/tinybox.js" type="text/javascript"></script>
        <!-- AdminLTE App -->
        <script src="<?php echo base_url('assets') ?>/js/AdminLTE/app.js" type="text/javascript"></script>
        <!-- AdminLTE for demo purposes -->
        <!--<script src="<?php echo base_url('assets') ?>/js/AdminLTE/demo.js" type="text/javascript"></script>-->

        <script type="text/javascript">
            function openBox(url, w, h) {
                tiny.box.show({iframe: url, width: w, height: h, close: true});
            }

            function closeBox(withReload) {
                withReload = null == withReload ? false : true;
                parent.tiny.box.hide();
                if (withReload) {
                    parent.location.reload();
                }
            }

            function setInputDateSearcOnLoad(obj) {
                alert(obj.val());
            }

            $(function() {
                //Error Msg
                $("#divErrorMsg").change(function() {
                    $(this).hide();
                    $(this).show(450);
                }).change();
                //Grid Table
                $("#tbl-grid").dataTable({
                    "bPaginate": false,
                    "bLengthChange": false,
                    "bFilter": false,
                    "bSort": false,
                    "bInfo": false,
                    "bAutoWidth": false
                });

                //Date range picker
                $(".elm-period").daterangepicker({
                    format: "DD/MM/YYYY",
                });

                $(".elm-select").css("width", "250px");

                $(".elm-date").parent(".input-group").css("width", "200px");
                $(".elm-date").datepicker({
                    format: "dd/mm/yyyy"
                });

                $("input.form-control").keyup(function() {
                    var parent = $(this).parent();
                    var lbl = parent.children("label").attr("for", $(this).attr("id"));
                    var elm_i = lbl.children("i");
                    if (Boolean($(this)[0].checkValidity) && (!$(this)[0].checkValidity())) {
                        parent.addClass("has-error");
                        lbl.css("color", "#333333");
                        elm_i.addClass("fa-warning");
                        elm_i.html("<span class=\"text-red\">&nbsp;&nbsp;Input tidak sesuai dengan format yang diharapkan !!!</span>");
                        elm_i.css("color", "#F39C12");
                    } else {
                        parent.removeClass("has-error");
                        lbl.css("color", "#333333");
                        elm_i.removeClass("fa-warning");
                        elm_i.html("");
                        elm_i.css("color", "#F39C12");
                    }
                });

                $('#formHris').find('input').each(function() {
                    var parent = $(this).parent();
                    var lbl = parent.children("label").attr("for", $(this).attr("id"));
                    var elm_i = lbl.children("i");
                    if ($(this).prop('required')) {
                        parent.removeClass("has-error");
                        lbl.css("color", "#333333");
                        elm_i.removeClass("fa-warning");
                        elm_i.html("");
                        elm_i.css("color", "#F39C12");
                    }
                });

                $('textarea[maxlength]').change(function() {
                    hitungChar($(this));
                }).change();

                $('textarea[maxlength]').keyup(function(e) {
                    hitungChar($(this));
                });

//                $('.elm-select-auto').selectize({
//                    create: false,
//                    sortField: {
//                        field: 'text',
//                        direction: 'asc'
//                    },
//                    dropdownParent: 'body'
//                });

                var $srcBisnisUnit = $('#srcBisnisUnit').selectize({
                    create: false,
                    sortField: {
                        field: 'text',
                        direction: 'asc'
                    },
                    dropdownParent: 'body'
                });
                if ($srcBisnisUnit[0] !== undefined) {
                    var srcBisnisUnitControl = $srcBisnisUnit[0].selectize;
//                console.log(srcBisnisUnitControl);
                    srcBisnisUnitControl.addItem("<?php echo $this->input->get_post('srcBisnisUnit') ?>");
                }
                $("#srcBisnisUnit").change(function() {
                    window.location.href = "?srcBisnisUnit=" + $(this).val();
                });


<?php
if (isset($additional_script)) {
    echo $additional_script;
}
?>


            });

            function hitungChar(obj) {
                var text = $(obj).val();
                var enters = text.split(/\r|\r\n|\n/);
                var enterCount = enters.length - 1;

                var temp = $(obj).val().replace(/\n/g, '');
                var textLen = (enterCount * 2) + temp.length;
                var charRemaining = 0;

                //console.log(temp.length);
                var max = parseInt($(obj).attr('maxlength'));
                if (max > 0) {
                    if (textLen > max) {
                        $(this).val($(obj).val().substr(0, $(obj).attr('maxlength')));
                    } else {
                        charRemaining = max - textLen;
                    }

                    $(obj).parent().find('.charsRemaining').html('You have ' + charRemaining + ' characters remaining');
                }
            }
        </script>


    </body>
</html>