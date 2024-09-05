<?php
header("Content-Type: text/html;charset=utf-8");
if(!is_file("./install/lock") && is_file("./install/index.php")){
	@header("location:install/index.php");
}
include("data/config.php");
include("inc/function.php");

$getaction = get("action");//获取参数

if($getaction=="loginout"){
	unset($_SESSION['uid']);
	unset($_SESSION['email']);
	unset($_SESSION['pageurl']);
	unset($_SESSION['new_name']);
	setcookie("userinfo", "", time()-3600);
	alertgourl("注销成功！","login.php");
}
if($getaction=="register" and Multiuser=="1"){ // 注册
	$form_name = "reg_form";
	$login_btn = "注册";
	$showlogin_form = showlogin("username").showlogin("email").showlogin("password");
	$first_input = "user_name";
}elseif($getaction=="getpassword"){//找回密码，发邮件
	$form_name = "getpassword_form";
	$login_btn = "发送";
	$showlogin_form = showlogin("email");
	$first_input = "user_email";
}elseif($getaction=="reset"){//重置密码
	if(empty($_SESSION['email'])){
		alertgourl("参数非法！","login.php");
	}
	$form_name = "reset_form";
	$login_btn = "重置";
	$showlogin_form = showlogin("email_session").showlogin("newpassword");
	$first_input = "newpassword";
}else{//默认
	$form_name = "log_form";
	$login_btn = "登录";
	$getaction = "login";
	$showlogin_form = showlogin("username").showlogin("password");
	$first_input = "user_name";
}
?>
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo SiteNameTitle;?></title>
<link rel="apple-touch-icon" sizes="180x180" href="<?php echo SiteURL;?>img/apple-touch-icon.png"><link rel="icon" type="image/png" sizes="32x32" href="<?php echo SiteURL;?>img/favicon-32x32.png"><link rel="icon" type="image/png" sizes="16x16" href="<?php echo SiteURL;?>img/favicon-16x16.png">
<link rel="stylesheet" href="css/login.css" />
</head>

<body>
<div class='login login-itlu-ui'>
	<div id="login">
		<h1><a href="//itlu.org/jizhang/" title="基于PHP多用户记账系统" tabindex="-1">基于PHP多用户记账系统</a></h1>
		<div id="login_error" style="display:none;"></div>		
		<form method="post" name="<?php echo $form_name;?>" id="<?php echo $form_name;?>">
			<?php echo $showlogin_form;?>
			<p class="submit">
				<input type="button" name="itlu-submit" id="itlu-submit" class="button button-primary button-large" value="<?php echo $login_btn;?>" />
			</p>
		</form>
		<?php if($getaction=="getpassword" or $getaction=="register"){?>
		<p id="nav"><a href="login.php">登录</a></p>
		<?php }else{?>
		<p id="nav"><a href="?action=getpassword">忘记密码？</a><?php if(Multiuser=="1"){?> | <a href="?action=register">注册账号</a><?php }?></p>
		<?php }?>
	</div>
	
</div>
<script type="text/javascript" src="js/jquery-2.1.1.min.js"></script>
<script type="text/javascript" src="js/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/login_check.js"></script>
<script>
try{document.getElementById('<?php echo $first_input;?>').focus();}catch(e){}
document.onkeydown = function(e){
	if(!e) e = window.event;
	if((e.keyCode || e.which) == 13){
		login_check('#<?php echo $form_name;?>','<?php echo $getaction;?>');
		return false;
	}
}
$("#login").append("<div id=\"sys"+"name\">&#35760;&#36134;&#31995;&#32479;&#20813;&#36153;&#29256; <?php echo $version;?></div>");
$("#itlu-submit").click(function(){
	login_check('#<?php echo $form_name;?>','<?php echo $getaction;?>');
});
</script>

</body>
</html>