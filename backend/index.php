<?php
//debug output
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);
require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/config.php';

$app = new Silex\Application();
$app['debug'] = true; //debug output

//settings
$locd = $GLOBALS["locd"];
$user = $GLOBALS["user"]; #FTW :O
$pass = $GLOBALS["pass"];
$dbna = $GLOBALS["dbna"];

//Person
//ID
//Get a person by id at all cemeteries
$app->get('/getPersonByCode/{code}',function($code) use($locd,$user,$pass,$dbna){
	//connect
	$db = new mysqli($locd, $user, $pass, $dbna);
	//query
	//municipality,
	$stmt = $db->prepare("select id,code,municipality,d.cemetery,type,dim1,dim2,dim3,dim4,familyName,firstName,dateOfDeath,
			dim1Name,dim2Name,dim3Name,dim4Name
		from DATA d
			join DimensionNames dn on dn.cemetery = d.cemetery
		where code = ?");
	$stmt->bind_param('s',$code);
	$stmt->execute();

	//bind & fetch
	$stmt->bind_result($id,$code,$municipality,$cemetery,$type,$dim1,$dim2,$dim3,$dim4,$familyName,$firstName,$dateOfDeath,
		$dim1Name,$dim2Name,$dim3Name,$dim4Name
	);
	$return = array();
	while($stmt->fetch()){
	    array_push($return,array("id" => $id,
	    	"code" => htmlentities($code), "municipality" => htmlentities($municipality),
	    	"cemetery" => htmlentities($cemetery),"type" => htmlentities($type),
	    	"firstName" => htmlentities($firstName), "familyName" => htmlentities($familyName),
	    	"dateOfDeath" => htmlentities($dateOfDeath),
	    	"dim1" => $dim1, "dim2" => $dim2, "dim3" => $dim3, "dim4" => $dim4,
	    	"dim1Name" => $dim1Name,"dim2Name" => $dim2Name,"dim3Name" => $dim3Name,"dim4Name" => $dim4Name
	    	));
	}

	//close connection
	$stmt->close();

	//encode
	return json_encode($return);
});

//Get a person by id at one cemetery
$app->get('/getPersonByCodeAtCemetery/{cemetery}/{code}',function($cemetery,$code) use($locd,$user,$pass,$dbna){
	//connect
	$db = new mysqli($locd, $user, $pass, $dbna);
	//query
	$stmt = $db->prepare("select id,code,municipality,d.cemetery,type,dim1,dim2,dim3,dim4,familyName,firstName,dateOfDeath,
			dim1Name,dim2Name,dim3Name,dim4Name
		from DATA d
			join DimensionNames dn on dn.cemetery = d.cemetery
		where code = ? AND d.cemetery = ?");
	$stmt->bind_param('ss',$code,$cemetery);
	$stmt->execute();

	//bind & fetch
	$stmt->bind_result($id,$code,$municipality,$cemetery,$type,$dim1,$dim2,$dim3,$dim4,$familyName,$firstName,$dateOfDeath,
		$dim1Name,$dim2Name,$dim3Name,$dim4Name
	);
	$return = array();
	while($stmt->fetch()){
	    array_push($return,array("id" => $id,
	    	"code" => htmlentities($code), "municipality" => htmlentities($municipality),
	    	"cemetery" => htmlentities($cemetery),"type" => htmlentities($type),
	    	"firstName" => htmlentities($firstName), "familyName" => htmlentities($familyName),
	    	"dateOfDeath" => htmlentities($dateOfDeath),
	    	"dim1" => $dim1, "dim2" => $dim2, "dim3" => $dim3, "dim4" => $dim4,
	    	"dim1Name" => $dim1Name,"dim2Name" => $dim2Name,"dim3Name" => $dim3Name,"dim4Name" => $dim4Name
	    	));
	}

	//close connection
	$stmt->close();

	//encode
	return json_encode($return);
});

//NAME (& year of death)
//Get a person by name at all cemeteries
$app->get('/getPersonByName/{name}', function($name) use($locd,$user,$pass,$dbna){
	//connect
	$db = new mysqli($locd, $user, $pass, $dbna);
	//echo "s: $name</br>";
	//get name and year
	if (preg_match('/[0-9]{1,4}/',$name,$year, PREG_OFFSET_CAPTURE )){
		//echo var_dump($year) . "</br>";
		//prep query
		$stmt = $db->prepare("select id,code,municipality,d.cemetery,type,dim1,dim2,dim3,dim4,familyName,firstName,dateOfDeath,
				dim1Name,dim2Name,dim3Name,dim4Name
			from DATA d
				join DimensionNames dn on dn.cemetery = d.cemetery
				where ( CONCAT (firstName, ' ', familyName) LIKE ?
					OR CONCAT(familyName , ' ' , firstName) LIKE ? )
				AND year(dateOfDeath) LIKE ?
		");

		//prep params
		$name = preg_replace('/\d/','',$name); //remove all digits in name
		$name = trim($name) . "%";//name starts with 

		$year = "%" . $year[0][0] . "%";//only the first year && contain

		//echo "y: $year </br>n: $name</br>";
		//bind params
		$stmt->bind_param('sss', $name, $name, $year); //only 
	}else{
		//prep query
		$stmt = $db->prepare("select id,code,municipality,d.cemetery,type,dim1,dim2,dim3,dim4,familyName,firstName,dateOfDeath,
			dim1Name,dim2Name,dim3Name,dim4Name
		from DATA d
			join DimensionNames dn on dn.cemetery = d.cemetery
			where ( CONCAT (firstName, ' ', familyName) LIKE ?
				OR CONCAT(familyName , ' ' , firstName) LIKE ? )
		");

		$name = trim($name) . "%";//name starts with

		//bind params
		$stmt->bind_param('ss', $name, $name);
	}

	//execute
	$stmt->execute();

	//bind & fetch
	$stmt->bind_result($id,$code,$municipality,$cemetery,$type,$dim1,$dim2,$dim3,$dim4,$familyName,$firstName,$dateOfDeath,
		$dim1Name,$dim2Name,$dim3Name,$dim4Name
	);
	$return = array();
	while($stmt->fetch()){
	    array_push($return,array("id" => $id,
	    	"code" => htmlentities($code), "municipality" => htmlentities($municipality),
	    	"cemetery" => htmlentities($cemetery),"type" => htmlentities($type),
	    	"firstName" => htmlentities($firstName), "familyName" => htmlentities($familyName),
	    	"dateOfDeath" => htmlentities($dateOfDeath),
	    	"dim1" => $dim1, "dim2" => $dim2, "dim3" => $dim3, "dim4" => $dim4,
	    	"dim1Name" => $dim1Name,"dim2Name" => $dim2Name,"dim3Name" => $dim3Name,"dim4Name" => $dim4Name
	    	));
	}

	//close connection
	$stmt->close();
	//encode
	return json_encode($return);
});

//Get a person by name at one cemetery
$app->get('/getPersonByNameAtCemetery/{cemetery}/{name}', function($cemetery,$name) use($locd,$user,$pass,$dbna){
	//connect
	$db = new mysqli($locd, $user, $pass, $dbna);
	//echo "s: $name</br>";
	//get name and year
	if (preg_match('/[0-9]{1,4}/',$name,$year, PREG_OFFSET_CAPTURE )){
		//echo var_dump($year) . "</br>";
		//prep query
		$stmt = $db->prepare("select id,code,municipality,d.cemetery,type,dim1,dim2,dim3,dim4,familyName,firstName,dateOfDeath,
			dim1Name,dim2Name,dim3Name,dim4Name
		from DATA d
			join DimensionNames dn on dn.cemetery = d.cemetery
			where ( CONCAT (firstName, ' ', familyName) LIKE ?
				OR CONCAT(familyName , ' ' , firstName) LIKE ? )
			AND year(dateOfDeath) LIKE ?
			AND d.cemetery = ?
		");

		//prep params
		$name = preg_replace('/\d/','',$name); //remove all digits in name
		$name = trim($name) . "%";//name starts with 

		$year = "%" . $year[0][0] . "%";//only the first year && contain

		//echo "y: $year </br>n: $name</br>";
		//bind params
		$stmt->bind_param('ssss', $name, $name, $year, $cemetery); //only 
	}else{
		//prep query
		$stmt = $db->prepare("select id,code,municipality,d.cemetery,type,dim1,dim2,dim3,dim4,familyName,firstName,dateOfDeath,
			dim1Name,dim2Name,dim3Name,dim4Name
		from DATA d
			join DimensionNames dn on dn.cemetery = d.cemetery
				where ( CONCAT (firstName, ' ', familyName) LIKE ?
					OR CONCAT(familyName , ' ' , firstName) LIKE ? )
				AND d.cemetery = ?
		");

		$name = trim($name) . "%";//name starts with

		//bind params
		$stmt->bind_param('sss', $name, $name, $cemetery);
	}

	$stmt->execute();

	//bind & fetch
	$stmt->bind_result($id,$code,$municipality,$cemetery,$type,$dim1,$dim2,$dim3,$dim4,$familyName,$firstName,$dateOfDeath,
		$dim1Name,$dim2Name,$dim3Name,$dim4Name
	);
	$return = array();
	while($stmt->fetch()){
	    array_push($return,array("id" => $id,
	    	"code" => htmlentities($code), "municipality" => htmlentities($municipality),
	    	"cemetery" => htmlentities($cemetery),"type" => htmlentities($type),
	    	"firstName" => htmlentities($firstName), "familyName" => htmlentities($familyName),
	    	"dateOfDeath" => htmlentities($dateOfDeath),
	    	"dim1" => $dim1, "dim2" => $dim2, "dim3" => $dim3, "dim4" => $dim4,
	    	"dim1Name" => $dim1Name,"dim2Name" => $dim2Name,"dim3Name" => $dim3Name,"dim4Name" => $dim4Name
	    	));
	}

	//close connection
	$stmt->close();

	//encode
	return json_encode($return);
});

//cemetery
//Get ALL the distinct cemeteries
$app->get('/getCemeteries', function() use($locd,$user,$pass,$dbna){
	//connect
	$db = new mysqli($locd, $user, $pass, $dbna);
	//query
	$res = $db->query("SELECT distinct(d.cemetery), dim1Name,dim2Name,dim3Name,dim4Name FROM DimensionNames dn join DATA d on dn.cemetery = d.cemetery");

	$return = array();
	while($row = $res->fetch_assoc()){
	    array_push($return,array("cemetery" => $row['cemetery'],
    		"dim1Name" => $row['dim1Name'],
    		"dim2Name" => $row['dim2Name'],
    		"dim3Name" => $row['dim3Name'],
    		"dim4Name" => $row['dim4Name'],
	    ));
	}

	//encode
	return json_encode($return);
});

//get ONE cemetery
$app->get('/getCemetery/{cemetery}', function($cemetery) use($locd,$user,$pass,$dbna){
	//connect
	$db = new mysqli($locd, $user, $pass, $dbna);
	//query
	$stmt = $db->prepare("SELECT distinct(d.cemetery), dim1Name,dim2Name,dim3Name,dim4Name FROM DimensionNames dn 
		join DATA d on dn.cemetery = d.cemetery 
		where d.cemetery = ?");

	$stmt->bind_param("s",$cemetery);
	$stmt->execute();

	$stmt->bind_result($cemetery,$dim1Name,$dim2Name,$dim3Name,$dim4Name);
	$return = array();
	while($stmt->fetch()){
	    array_push($return,array("cemetery" => $cemetery,
    		"dim1Name" => $dim1Name,
    		"dim2Name" => $dim2Name,
    		"dim3Name" => $dim3Name,
    		"dim4Name" => $dim4Name,
	    ));
	}

	$stmt->close();

	//encode
	return json_encode($return);
});


$app->run();