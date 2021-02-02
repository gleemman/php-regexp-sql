<?php

function updatePdo($table,$fields,$where = '',$count=0) {

	$dbms='mysql';     //数据库类型
	$host='localhost'; //数据库主机名
	$dbName='dev_ycwy';    //使用的数据库
	$user='root';      //数据库连接用户名
	$pass='root';          //对应的密码
	$dsn="$dbms:host=$host;dbname=$dbName";


	//默认这个不是长连接，如果需要数据库长连接，需要最后加一个参数：array(PDO::ATTR_PERSISTENT => true) 变成这样：
	$db = new PDO($dsn, $user, $pass, array(PDO::ATTR_PERSISTENT => true));


	$sql = 'UPDATE ' . $table . ' SET ';
	$where = str_replace(array('where','WHERE'),array(),$where);

	$searchs = $replaces = $values = array();

	
	//更新值
	$kvs = $xkvs = array();
	if (is_array($fields)) foreach($fields as $key=>$value){
		$xkey = ':value_'.$key;
		$values[$xkey] = $value;
		$kvs[] = "`$key`=$xkey";
	}
	if ($kvs == false) return 0;
	$sql .= join(',',$kvs);



	//条件:AND OR !=
	//$where = str_replace(array('\\'),array('\\\\'),$where);
	$where = ' '.trim($where).' ';


	$regexp = '/\s*`?([a-zA-Z\d+_]+)`?\s*(=|<>|!=|LIKE)\s*(?(?=[\'\"])([\'\"])(.*?(?(?<=\\\)\\\\|(?<!\\\)))\3|(\d+))\s*/is';
	preg_match_all($regexp,$where,$ms);
	var_dump($ms);
	if (isset($ms[1]) && is_array($ms[1])) foreach($ms[1] as $k=>$v) {

		$searchs[] = $_sv = $ms[0][$k];
		$vkey = ':where_'.$k;
		$replaces[] = ' `'.$ms[1][$k].'` '.$ms[2][$k].' '.$vkey.' ';
		$values[$vkey] = strlen($ms[5][$k])?$ms[5][$k]:$ms[4][$k];

	}

	
	$where = trim(str_replace($searchs,$replaces,$where));
	if ($where) {
		$sql .= ' WHERE '.$where;
	}

	if ($count) {
		$sql .= " LIMIT ".$count;
	}
	var_dump($values);
	
	//return 0;

	$stmt = $db->prepare($sql);

	if ($stmt->execute($values)) {

		return $stmt->rowCount();

	} else {

		$err = $stmt->errorInfo();
		if (isset($err[1]) == false) {
			$err[1] = $stmt->errorCode();
		}
		return [$sql,$err[1],$err[2]];
	}
	return 0;
}

var_dump(updatePdo("tb_unicom_area",['city_code'=>113],'`district`="东城\\\"区\\\" AND (city = "") AND city_no=110100'));

