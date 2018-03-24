<?php
	
	require './function.php';

	header("content-type:text/html;charset=utf-8");
	$dsn="mysql:dbname=hyw;host=localhost";
	$db_user='root';
	$db_pass='root';
    
	try{
	   $pdo = new PDO($dsn,$db_user,$db_pass);
	   $pdo->query("SET NAMES utf8");
	}catch(PDOException $e){
	   echo '数据库连接失败'.$e->getMessage();
	}

	//新增
	// $sql="insert into buyer (username,password,email) values ('ff','123456','admin@admin.com')";
	// $res=$pdo->exec($sql);
	// echo '影响行数：'.$res;

	// //修改
	// $sql="update buyer set username='ff123' where id>3";
	// $res=$pdo->exec($sql);
	// echo '影响行数：'.$res;
	// //查询

	// //删除
	// $sql="delete from buyer where id>5";
	// $res=$pdo->exec($sql);
	// echo '影响行数：'.$res;	
	$sql = "select * from tp_config";
	$config = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	$order_over_time = $config[37]['value']*60*60;
	//$order_over_time = 60*5;
	$temp_over_time  = $config[71]['value']*60*60;
	// $temp_over_time  = 5;

    //年月租订单过期检查
    $sql   = "SELECT order_id,user_id,order_sn,grab_number 
              FROM tp_order 
              WHERE UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(add_time) >= {$order_over_time} AND is_over_time = 0 AND order_status = 0";
    $Order = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    if($Order){
        foreach($Order as $k => $v){
            $order_id_str[] = $v['order_id'];
        }

        $order_id_str = implode(',',$order_id_str);
        $sql  = "UPDATE tp_order SET is_over_time = 1,is_completed = 1,order_status = (CASE WHEN grab_number = 0 THEN 2 WHEN grab_number > 0 THEN 3 END) WHERE order_id IN({$order_id_str})";
        $res = $pdo->exec($sql);

        if($res){
            foreach($Order as $k => $v){
                if($v['grab_number'] == 0){
                    $type = ['type'=>3,'order_id'=>$v['order_id'],'order_status'=>2];
                    $content = '您有订单无人接单而失效，点击查看详情！';
                    $content1 = $v['order_sn'] . '订单无人接单，订单失效，请到个人中心-订单中心查看详情！';
                    Jpush($v['user_id'],$type,$content,$content1,true);//年月租过期无人接单，消息推送
                }
            }
            echo 123;
        }
    }


    //临时租订单过期检查
    $sql  = "SELECT temp_id,user_id,temp_sn 
             FROM tp_temporary 
             WHERE UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(add_time) >= {$temp_over_time} AND status = 1";
    $Temp = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    if($Temp){
        foreach($Temp as $k => $v){
            $temp_id_str[] = $v['temp_id'];
        }

        $temp_id_str = implode(',',$temp_id_str);
        $sql  = "UPDATE tp_temporary SET status = 4 WHERE temp_id IN({$temp_id_str})";
        $res = $pdo->exec($sql);

        if($res){
            foreach($Temp as $k => $v){
                $type = ['type'=>11,'temp_id'=>$v['temp_id']];
                $content = '您有订单无人接单而失效，点击查看详情！';
                $content1 = $v['temp_sn'] . '订单无人接单，订单失效，请到个人中心-订单中心查看详情！';
                Jpush($v['user_id'],$type,$content,$content1,true);//临时租过期无人接单，消息推送
            }
        }
        echo 456;
    }

	echo 111;


