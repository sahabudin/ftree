<html>
<head>
<script type="text/javascript" src="./jquery.min.js"></script>
<script type="text/javascript" src="./jquery-ui.min.js"></script>
<script type="text/javascript" src="./jquery.jsPlumb-1.3.3-all-min.js "></script>
<style>
.user {border:1px solid black; text-align:center; position:relative; float:left; padding:5px}
.hspacer {position:relative; width:2%; height:4%; float:left}
</style>
<script type="text/javascript">
function addUser(id,type)
{
	var name = prompt("Enter a name:");
	
	if(name.length<0)
		return;

	var form = document.forms["form0"];
	document.getElementById("name").value=name;
	document.getElementById("currNode").value=id;
	document.getElementById("type").value=type;
	document.forms["form0"].submit();
}

jsPlumb.ready(function() {
	var fillColor = "gray";
	
	jsPlumb.Defaults.Connector = [ "Bezier", { curviness:50 } ];
	jsPlumb.Defaults.DragOptions = { cursor: "pointer", zIndex:2000 };
	jsPlumb.Defaults.PaintStyle = { strokeStyle:"gray", lineWidth:2 };
	jsPlumb.Defaults.EndpointStyle = { radius:5, fillStyle:"gray" };
	jsPlumb.Defaults.Anchors =  [ "BottomCenter", "TopCenter" ];
	jsPlumb.Defaults.Container = $("#ftree");
	
	var arrowCommon = { foldback:0.7, fillStyle:fillColor, width:14 };
	var overlays = [
		[ "Arrow", { location:0.7 }, arrowCommon ]
	];

<?php
include('common.php');
if(isset($_POST['name']) && isset($_POST['currNode']) && isset($_POST['type']))
{
	$name = $_POST['name'];
	$currNode = $_POST['currNode'];
	$type = $_POST['type'];
	
	if($type=='child')
	{
		$b=mysql_query("SELECT * FROM ftree where id=".$currNode);
		$p=new User(mysql_fetch_assoc($b));
		$m = $p->getSpouse()->getId();
		if($m==0) $m=-1;
		$a = mysql_query("INSERT INTO  ftree(`id`,`name`,`father`,`mother`,`spouse`)
		VALUES(NULL ,'".$name."', '".$currNode."', '".$m."','-1')");
	}
	else if($type=='spouse')
	{
		$a = mysql_query("INSERT INTO  ftree(`id`,`name`,`father`,`mother`,`spouse`)
		VALUES(NULL ,'".$name."', '-1', '-1','".$currNode."')");
		$s = mysql_query("SELECT * FROM ftree WHERE spouse=".$currNode);
		$u = new User(mysql_fetch_assoc($s));
		$b = mysql_query("UPDATE  ftree SET spouse=".$u->getId()." WHERE  id=".$currNode);
	}
}

$vspacer = "<br><br><br><br><br><br><br><br>\n";
$hspacer = "<div class=\"hspacer\"></div>";
$body="\n<body onunload=\"chart.unload();\">
<div id=\"ftree\">
<form id=\"form0\" action=\"index.php\" method=\"post\">
<input type=\"hidden\" id=\"currNode\" name=\"currNode\" value =\"\"/>
<input type=\"hidden\" id=\"name\" name=\"name\" value =\"\"/>
<input type=\"hidden\" id=\"type\" name=\"type\" value =\"\"/>
</form>\n";
$script="";

$q=mysql_query("SELECT * FROM ftree where father=-1 AND mother=-1 ORDER BY id ASC");
$root=new User(mysql_fetch_assoc($q));
$next=array(array($root));

while(sizeof($next))
{
	$nnext=array();
	foreach($next as $node)
	{
		foreach($node as $nnode)
		{
			$tmp = printNode($nnode);
			$body=$body.$tmp['body'];
			$script=$script.$tmp['script'];
			array_push($nnext,$tmp['next']);
		}
		
		if(sizeof($node))
			$body=$body.$hspacer;
	}
	
	if(sizeof($nnext))
		$body=$body.$vspacer;
	$next=$nnext;
}

$script=$script."\n});\n</script></head>";
echo $script;
echo $body;
?>
</div>
</body>
</html>