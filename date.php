<?php
header('Content-type:text/json;charset=utf-8');
include_once("data/config.php");
include_once("inc/function.php");
loginchk($userid);
$gotourl = "";
$success = "0";
$getaction = get("action");
if($getaction=="getclassify"){	
	header('Content-type:text/html;charset=utf-8');
	$classtype = get("classtype");
	$classid = get("classid");
	$sql = "select * from ".TABLE."account_class where userid='$userid' and classtype='$classtype'";
    $query = mysqli_query($conn,$sql);
    while($row = mysqli_fetch_array($query)){
		if($row["classid"] <> $classid){
			echo "<option value='".$row["classid"]."'>".$row["classname"]."</option>";
		}
	}
}
if($getaction=="addbank"){
	$bankname = post("bankname");
	$bankaccount = post("bankaccount");
	$balancemoney = post("balancemoney","0");
	if(empty($bankname) or empty($bankaccount)){
		$error_code = "缺少参数！";
	}elseif(!is_numeric($balancemoney)){
		$error_code = "金额非法！";
	}else{
		$sql = "select * from ".TABLE."bank where bankname='$bankname' and userid='$userid'";
		$query = mysqli_query($conn,$sql);
		$attitle = is_array($row = mysqli_fetch_array($query));
		if($attitle){
			$error_code = "该账户名称已存在！";
		}else{			
			$sql = "insert into ".TABLE."bank (bankname, bankaccount, balancemoney, userid) values ('$bankname', '$bankaccount', '$balancemoney', '$userid')";
			$query = mysqli_query($conn,$sql);
			if($query){
				$success = "1";
				$error_code = "保存成功！";
				$gotourl = "bank.php";						
			}else{
				$error_code = "保存失败！";
			}
		}
	}
	$data = '{"code":"' .$success. '","error_msg":"' .$error_code.'","url":"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="modifybank"){
	$bankname = post("bankname");
	$bankaccount = post("bankaccount");
	$bankid = post("bankid");
	$balancemoney = post("balancemoney","0");
	if(empty($bankname) or empty($bankaccount) or empty($bankid)){
		$error_code = "缺少参数！";
	}else{
		$sql = "update ".TABLE."bank set bankname='".$bankname."',bankaccount='".$bankaccount."' where bankid=".$bankid;
		$result = mysqli_query($conn,$sql);
		if ($result) {
			$success = "1";
			$error_code = "保存成功！";
			$gotourl = "bank.php";
		} else {
			$error_code = "保存失败！";
		}	
	}
	$data = '{"code":"' .$success. '","error_msg":"' .$error_code.'","url":"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="deletebank"){
	header('Content-type:text/html;charset=utf-8');
	$bankid = get("bankid");
	if(empty($bankid) || !is_numeric($bankid)){
		$error_code = "缺少参数或参数非法！";
	}else{
		$sql = "select acid from ".TABLE."account where bankid='$bankid' and userid='$userid'";
		$query = mysqli_query($conn,$sql);
		if ($row = mysqli_fetch_array($query)) {
			$error_code = "有关联数据，不能删除！";
		} else {
			$sql = "delete from ".TABLE."bank where bankid=".$bankid;
			if (mysqli_query($conn,$sql)){
				$error_code = "删除成功！";
			}else{
				$error_code = "删除失败！";
			}			
		}
	}				
	echo $error_code;
}
if($getaction=="addrecord"){
	$classid = post("classid");
	$money = post("money");
	$zhifu = post("zhifu");
	$remark = post("remark");
	$bankid = post("bankid");
	$proid = post("proid");
	$addtime = strtotime(post("time"));
	if(empty($classid) or empty($money) or empty($zhifu) or empty($proid)){
		$error_code = "缺少参数！";
	}elseif(!is_numeric($money)){
		$error_code = "金额非法！";
	}else{
		$sql = "insert into ".TABLE."account (acmoney, acclassid, actime, acremark, userid, zhifu, bankid, proid) values ('$money', '$classid', '$addtime', '$remark', '$userid', '$zhifu', '$bankid', '$proid')";
		$query = mysqli_query($conn,$sql);
		if($query){
			if($bankid>0){money_int_out($bankid,$money,$zhifu);}
			$success = "1";
			$error_code = "保存成功！";
			if($zhifu=="1"){
				$gotourl = "add.php?action=income";
			}else{
				$gotourl = "add.php?action=pay";
			}
			setcookie("add_itlu_classid_".$userid, $classid, time()+86400*3);
			setcookie("add_itlu_bankid_".$userid, $bankid, time()+86400*3);
			setcookie("add_itlu_proid_".$userid, $proid, time()+86400*3);
		}else{
			$error_code = "保存失败！";
		}		
	}
	$data = '{"code":"' .$success. '","error_msg":"' .$error_code.'","url":"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="saverecord"){
	$id = post("edit-id");
	$money = post("edit-money");
	$old_money = post("old-money");
	$remark = post("edit-remark");
	$proid = post("edit-proid");
	$old_bankid = post("old-bank-id");
	$new_bankid = post("edit-bankid");
	$zhifu = post("edit-zhifu");
	$addtime = strtotime(post("edit-time"));
	if(empty($id) or empty($money) or empty($proid) or empty($new_bankid)){
		$error_code = "缺少参数！";
	}elseif(!is_numeric($money)){
		$error_code = "金额非法！";
	}else{
		$sql = "update ".TABLE."account set acmoney='".$money."',acremark='".$remark."',actime='".$addtime."',bankid='".$new_bankid."',proid='".$proid."' where acid='".$id."' and userid='".$userid."'";
		$result = mysqli_query($conn,$sql);
		if($result){
			if($zhifu==2){//支出
				if($old_bankid==$new_bankid && $old_bankid>0){money_int_out($old_bankid,$old_money,"1");money_int_out($old_bankid,$money,"2");}
				if($old_bankid<>$new_bankid && $old_bankid>0){money_int_out($old_bankid,$money,"1");}
				if($old_bankid<>$new_bankid && $new_bankid>0){money_int_out($new_bankid,$money,"2");}
			}else{//收入
				if($old_bankid==$new_bankid && $old_bankid>0){money_int_out($old_bankid,$old_money,"2");money_int_out($old_bankid,$money,"1");}
				if($old_bankid<>$new_bankid && $old_bankid>0){money_int_out($old_bankid,$money,"2");}
				if($old_bankid<>$new_bankid && $new_bankid>0){money_int_out($new_bankid,$money,"1");}
			}
			$success = "1";
			$error_code = "保存成功！";
		} else {
			$error_code = "保存失败！";
		}		
	}
	$data = '{"code":"' .$success. '","error_msg":"' .$error_code.'","url":"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="deleterecord"){
	header('Content-type:text/html;charset=utf-8');
	$get_id = get("id");
	if(empty($get_id) || !is_numeric($get_id)){
		$error_code = "缺少参数或参数非法！";
	}else{
		$sql = "select bankid,zhifu,acmoney from ".TABLE."account where acid='$get_id' and userid='$userid'";
		$query = mysqli_query($conn,$sql);
		if($row = mysqli_fetch_array($query)){
			if($row["bankid"]>0){
				$zhifu = "2";
				if($row["zhifu"]=="2"){$zhifu = "1";}
				money_int_out($row["bankid"],$row["acmoney"],$zhifu);
			}
		}
		$del_sql = "delete from ".TABLE."account where acid='$get_id' and userid='$userid'";
		if(mysqli_query($conn,$del_sql)){
			$error_code = "删除成功！";
		}else{
			$error_code = "删除失败！";
		}
	}				
	echo $error_code;
}
if($getaction=="deleterecordAll"){
	if(isset($_POST["del_id"]) && $_POST["del_id"] != ""){
		$del_id = implode(",",$_POST['del_id']);
		$sql = "delete from ".TABLE."account where userid='$userid' and acid in ($del_id)";
		if (mysqli_query($conn,$sql)){
			$success = "1";
			$error_code = "删除成功！";
		}else{
			$error_code = "删除失败！";
		}	
	}else{
		$error_code = "参数不正确！";
	}			
	$data = '{"code":"' .$success. '","error_msg":"' .$error_code.'","url":"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="changeclassify"){
	$classid = post("classid");
	$newclassid = post("newclassid");
	if(empty($newclassid) or $newclassid=="0" or empty($classid)){
		$error_code = "缺少参数或者未选择目标分类！";
	}else{
		$sql = "update ".TABLE."account set acclassid='$newclassid' where acclassid='$classid' and userid='$userid'";
		$query = mysqli_query($conn,$sql);
		if ($query) {
			$success = "1";
			$error_code = "更改分类成功！";
			$gotourl = "classify.php";
		} else {
			$error_code = "保存失败！";
		}		
	}
	$data = '{"code":"' .$success. '","error_msg":"' .$error_code.'","url":"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="deleteclassify"){
	header('Content-type:text/html;charset=utf-8');
	$classid = get("classid");
	if(empty($classid)){
		$error_code = "缺少参数！";
	}else{
		$sql = "select acid from ".TABLE."account where acclassid='$classid' and userid='$userid'";
		$query = mysqli_query($conn,$sql);
		if ($row = mysqli_fetch_array($query)) {
			$error_code = "在此分类下有账目，请将账目转移到其他分类";
		} else {
			$sql = "delete from ".TABLE."account_class where classid=".$classid;
			if (mysqli_query($conn,$sql)){
				$error_code = "分类删除成功！";
			}else{
				$error_code = "删除失败！";
			}			
		}
	}
	echo $error_code;
}
if($getaction=="addclassify"){
	$classname = post("classname");
	$classtype = post("classtype");
	if(empty($classname)){
		$error_code = "分类名称不能为空！";
	}elseif(strlen($classname)>18){
		$error_code = "分类名称不能大于6个字！";
	}else{
		$sql = "select * from ".TABLE."account_class where classname='$classname' and classtype='$classtype' and userid='$userid'";
		$query = mysqli_query($conn,$sql);
		$attitle = is_array($row = mysqli_fetch_array($query));
		if ($attitle) {
			$error_code = "该名称的分类已经存在！";
		}
		else {
			$sql = "insert into ".TABLE."account_class (classname, classtype, userid) values ('$classname', '$classtype',$userid)";
			$query = mysqli_query($conn,$sql);
			if ($query) {
				$error_code = "保存成功！";
				$success = "1";
				$gotourl = "classify.php";
			} else {
				$error_code = "保存失败！";
			}
		}	
		
	}
	$data = '{"code":"' .$success. '","error_msg":"' .$error_code.'","url":"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="modifyclassify"){
	$classname = post("classname");
	if(empty($classname)){
		$error_code = "分类名称不能为空！";
	}elseif(strlen($classname)>18){
		$error_code = "分类名称不能大于6个字！";
	}else{
		$sql = "select * from ".TABLE."account_class where classname='$classname' and classid<>'$_POST[classid]' and userid='$userid'";
		$query = mysqli_query($conn,$sql);
		$attitle = is_array($row = mysqli_fetch_array($query));
		if($attitle){
			$error_code = "该名称的分类已经存在！";
		}else{
			$sql = "UPDATE ".TABLE."account_class set classname='$classname' , classtype=$_POST[classtype] where userid='$userid' and classid=".$_POST["classid"];
			$query = mysqli_query($conn,$sql);
			if($query){
				$error_code = "保存成功！";
				$success = "1";
				$gotourl = "classify.php";
			}else{
				$error_code = "保存失败！";
			}
		}		
	}
	$data = '{"code":"' .$success. '","error_msg":"' .$error_code.'","url":"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="addprogram"){
	$classname = post("proname");
	$orderid = post("orderid");
	if(empty($classname)){
		$error_code = "名称不能为空！";
	}elseif(strlen($classname)>30){
		$error_code = "名称不能大于10个字！";
	}elseif(!is_numeric($orderid)){
		$error_code = "排序ID只能输入数字！";
	}else{
		$sql = "select * from ".TABLE."program where proname='$classname' and userid='$userid'";
		$query = mysqli_query($conn,$sql);
		$attitle = is_array($row = mysqli_fetch_array($query));
		if ($attitle) {
			$error_code = "该名称的项目已经存在！";
		}
		else {
			$sql = "insert into ".TABLE."program (proname, orderid, userid) values ('$classname', '$orderid', $userid)";
			$query = mysqli_query($conn,$sql);
			if ($query) {
				$error_code = "保存成功！";
				$success = "1";
				$gotourl = "program.php";
			} else {
				$error_code = "保存失败！";
			}
		}	
		
	}
	$data = '{"code":"' .$success. '","error_msg":"' .$error_code.'","url":"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="modifyprogram"){
	$classname = post("proname");
	$orderid = post("orderid");
	$proid = post("proid");
	if(empty($classname)){
		$error_code = "名称不能为空！";
	}elseif(strlen($classname)>30){
		$error_code = "名称不能大于10个字！";
	}elseif(!is_numeric($orderid)){
		$error_code = "排序ID只能输入数字！";
	}else{
		$sql = "select * from ".TABLE."program where proname='$classname' and proid<>'$proid' and userid='$userid'";
		$query = mysqli_query($conn,$sql);
		$attitle = is_array($row = mysqli_fetch_array($query));
		if($attitle){
			$error_code = "该名称的分类已经存在！";
		}else{
			$sql = "UPDATE ".TABLE."program set proname='$classname' , orderid='$orderid' where userid='$userid' and proid=".$proid;
			$query = mysqli_query($conn,$sql);
			if($query){
				$error_code = "保存成功！";
				$success = "1";
				$gotourl = "program.php";
			}else{
				$error_code = "保存失败！";
			}
		}		
	}
	$data = '{"code":"' .$success. '","error_msg":"' .$error_code.'","url":"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="deleteprogram"){
	header('Content-type:text/html;charset=utf-8');
	$classid = get("proid");
	if(empty($classid)){
		$error_code = "缺少参数！";
	}else{
		$sql = "select bankid from ".TABLE."bank where bankaccount='$classid' and userid='$userid'";
		$query = mysqli_query($conn,$sql);
		if ($row = mysqli_fetch_array($query)) {
			$error_code = "在此项目下有员工，不能删除项目！";
		} else {
			$sql = "delete from ".TABLE."program where proid=".$classid;
			if (mysqli_query($conn,$sql)){
				$error_code = "删除成功！";
			}else{
				$error_code = "删除失败！";
			}			
		}
	}
	echo $error_code;
}
if($getaction=="changeuser"){
	$type = get("m");
	$uid = get("uid");
	if(empty($type) or empty($uid)){
		$error_code = "参数不完整！";
	}else{
		if($type=="noallow"){
			$u_sql = "Isallow=1";
			$itlu_word = "禁用";
		}else{
			$u_sql = "Isallow=0";
			$itlu_word = "启用";
		}
		$update_sql = "update ".TABLE."user set ".$u_sql." where uid='$uid'";
        $update_query = mysqli_query($conn,$update_sql);
		if($update_query){
			$error_code = $itlu_word."成功！";
		}else{
			$error_code = "操作失败！";
		}
		$success = "1";
		$gotourl = "users.php";
	}
	$data = '{"code":"' .$success. '","error_msg":"' .$error_code.'","url":"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="changepassword"){
	$uid = get("uid");
	$name = get("name");
	if(empty($uid) or empty($name)){
		$error_code = "参数不完整！";
	}else{
		$newpassword = "12345678";
		$update_time = strtotime("now");
		$salt = md5($name.$update_time.$newpassword);
		$user_pass = hash_md5($newpassword,$salt);
		$u_sql = "password='$user_pass',salt='$salt'";
		$update_sql = "update ".TABLE."user set ".$u_sql." where uid='$uid'";
        $update_query = mysqli_query($conn,$update_sql);
		if($update_query){
			$success = "1";
			$error_code = "操作成功！密码重置为：12345678";
			$gotourl = "users.php";
		}else{
			$error_code = "出错啦，写入数据库时出错！";
		}
	}
	$data = '{"code":"' .$success. '","error_msg":"' .$error_code.'","url":"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="updateuser"){
	$password = post_pass("password");
	$newpassword = post_pass("newpassword");
	$email = post("email");
	//$action = post("action");
	if(empty($password)){
		$error_code = "密码不能为空！";
	}elseif(strlen($password)<6){
		$error_code = "密码不能小于6个字符！";
	}elseif((!empty($email)) && (checkemail($email) == false)){
		$error_code = "邮箱格式错误！";
	}elseif(!empty($newpassword) && strlen($newpassword)<6){
		$error_code = "新密码不能小于6个字符！";
	}else{
		$sql = "SELECT * FROM ".TABLE."user where uid='$userid'";
		$query = mysqli_query($conn,$sql);
		$row = mysqli_fetch_array($query);
		$db_salt = $row["salt"];
		if($row["password"] == hash_md5($password,$db_salt)){			
			$update_time = strtotime("now");
			$u_sql = "utime='$update_time'";
			if(!empty($email)){
				$u_sql = $u_sql.",email='$email'";
			}
			if(!empty($newpassword)){				
				$salt = md5($row["username"].$update_time.$newpassword);
				$user_pass = hash_md5($newpassword,$salt);
				$u_sql = $u_sql.",password='$user_pass',salt='$salt'";
			}
			$update_sql = "update ".TABLE."user set ".$u_sql." where uid='$userid'";
            $update_query = mysqli_query($conn,$update_sql);
			if($update_query){
				$success = "1";
				$userinfo_update = array("userid"=>"$userid","username"=>"$row[username]","useremail"=>"$email","regtime"=>"$row[addtime]","updatetime"=>"$update_time","isadmin"=>"$row[Isadmin]");
				$userinfo = AES::encrypt($userinfo_update, $sys_key);
				setcookie("userinfo", $userinfo, time()+86400);
				$error_code = "信息修改成功！";
				$gotourl = "system.php?action=sys";
			}else{
				$error_code = "出错啦，写入数据库时出错！";
			}
		}else{
			$error_code = "旧密码错误！";
		}		
	}
	$data = '{"code":"' .$success. '","error_msg":"' .$error_code.'","url":"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="export"){
	header('Content-type:text/html;charset=utf-8');
	$classid = get('classid','all');
	$starttime = get('starttime');
	$endtime = get('endtime');
	$startmoney = get('startmoney');
	$endmoney = get('endmoney');
	$remark = get('remark');
	$bankid = get('bankid');
	$sql = "select a.zhifu,a.acmoney,a.actime,a.acremark,a.bankid,a.proid,b.classname from ".TABLE."account as a INNER JOIN ".TABLE."account_class as b ON b.classid=a.acclassid";
	if($classid == "all"){
		
	}elseif($classid == "pay"){
		$sql .= " and a.zhifu = 2 ";
	}elseif($classid == "income"){
		$sql .= " and a.zhifu = 1 ";
	}else{
		$sql .= " and a.acclassid = '".$classid."' ";
	}
	if(!empty($bankid)){
		$sql .= " and bankid = '".$bankid."' ";
	}
	if(!empty($starttime)){
		$sql .= " and actime >= '".strtotime($starttime." 00:00:00")."' ";
	}
	if(!empty($endtime)){
		$sql .= " and actime <= '".strtotime($endtime." 23:59:59")."' ";
	}
	if(!empty($startmoney)){
		$sql .= " and acmoney >= '".$startmoney."' ";
	}
	if(!empty($endmoney)){
		$sql .= " and acmoney <= '".$endmoney."' ";
	}
	$result = mysqli_query($conn,$sql);
    $str = "收支,分类,项目,账户,金额,时间,备注\n";
    $str = iconv('utf-8','gb2312',$str);
    while ($row = mysqli_fetch_array($result)) {
        $classname = iconv('utf-8','gb2312',$row['classname']);
        if ($row['zhifu'] == 1) {
            $shouzhi = iconv('utf-8','gb2312',"收入");
        } else {
            $shouzhi = iconv('utf-8','gb2312',"支出");
        }
        $money = $row['acmoney'];
        $time = date("Y-m-d H:i",$row['actime']);		
		if($row['acremark']<>""){
			$remark = iconv('utf-8','gbk',$row['acremark']);
		}else{
			$remark = "";
		}
		$proname = programname($row['proid'],$userid,"默认项目");
		$proname = iconv('utf-8','gb2312',$proname);
		$bankname = bankname($row['bankid'],$userid,"默认账户");
		$bankname = iconv('utf-8','gb2312',$bankname);
        $str .= $shouzhi.",".$classname.",".$proname.",".$bankname.",".$money.",".$time.",".$remark."\n";
    }
    $filename = date('YmdHis').'.csv';
    export_csv($filename,$str);
}
if($getaction=='import') {
	header('Content-type:text/html;charset=utf-8');
    if(empty($_FILES['file']['tmp_name'])){alertgourl("请选择文件！","int_out.php");}	
    $filename = $_FILES['file']['tmp_name'];
	$filetype = (pathinfo($_FILES['file']['name']));
	if($filetype['extension'] != "csv"){alertgourl("文件格式不对，请重新选择！","int_out.php");}
    $handle = fopen($filename, 'r');
    $result = input_csv($handle);
    $len_result = count($result);
    if ($len_result <= 1){alertgourl("你的文件没有任何数据！","int_out.php");}
	$data_values = "";
	$insert_count = 0;
    for ($i = 1; $i < $len_result; $i++) {
		$money = $result[$i][3];
		if($money<=0){continue;}
		$bankid = 0; //涉及账户之间加减,默认仅支持0
        $shouzhi = iconv('gb2312', 'utf-8', $result[$i][0]);
        if ($shouzhi == "收入") {
            $shouzhi = "1";
        }else{
            $shouzhi = "2";
        }
		$classify = iconv('gb2312', 'utf-8', $result[$i][1]);
        $sql = "select classid from ".TABLE."account_class where classname='$classify' and userid='$userid'";
        $query = mysqli_query($conn,$sql);
		$row = mysqli_fetch_array($query);
        if ($row["classid"]){
			$acclassid = $row["classid"];
        }else{
            $sqladd = "insert into ".TABLE."account_class (classname, classtype, userid) values ('$classify', '$shouzhi', '$userid')";
            $queryadd = mysqli_query($conn,$sqladd);
            $acclassid = mysqli_insert_id($conn);
        }        
        $addtime = strtotime($result[$i][4]);
        $remark = iconv('gb2312', 'utf-8', $result[$i][5]);
		$insert_count++;
        $data_values .= "('$money','$acclassid','$addtime','$remark','$userid','$shouzhi','$bankid'),";
    }
    $data_values = substr($data_values,0,-1);
    fclose($handle);
    $query = mysqli_query($conn,"insert into ".TABLE."account (acmoney,acclassid,actime,acremark,userid,zhifu,bankid) values $data_values");
    if($query){
		$word = "导入成功！导入".$insert_count."条";
		alertgourl($word,"int_out.php");
    }else{
		alertgourl("导入失败，请检查文件格式！","int_out.php");
    }
}
if($getaction=='updatesystem'){
	$filepath = "data/config.php";
	$info = vita_get_url_content($filepath);
	foreach($_POST as $k=>$v){
        $info=preg_replace("/define\(\"{$k}\",\".*?\"\)/","define(\"{$k}\",\"{$v}\")",$info);
    }
    file_put_contents($filepath,$info);
	$success = "1";
	$error_code = "信息修改成功！";
	$gotourl = "system.php?action=sys";
	$data = '{"code":"' .$success. '","error_msg":"' .$error_code.'","url":"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=='updatesmtp'){
	$filepath = "inc/smtp_config.php";
	$info = vita_get_url_content($filepath);
	foreach($_POST as $k=>$v){
        $info=preg_replace("/define\(\"{$k}\",\".*?\"\)/","define(\"{$k}\",\"{$v}\")",$info);
    }
    file_put_contents($filepath,$info);
	$success = "1";
	$error_code = "信息修改成功！";
	$gotourl = "system.php?action=smtp";
	$data = '{"code":"' .$success. '","error_msg":"' .$error_code.'","url":"' .$gotourl.'"}';
    echo json_encode($data);
}
?>