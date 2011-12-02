<?php
if (!$dbh=mysql_connect('localhost','root',''))
	die("mysql_connect() failed\n");
	
mysql_select_db('test');

class User
{
	private $id;
	private $name;
	private $father;
	private $mother;
	private $spouse;
	
	function __construct($user)
	{
		$this->id=$user['id'];
		$this->name=$user['name'];
		$this->father=$user['father'];
		$this->mother=$user['mother'];
		$this->spouse=$user['spouse'];
	}
		
	function getId()
	{
		return $this->id;
	}
	
	function getName()
	{
		return $this->name;
	}
	
	function getFather()
	{
		$ft=mysql_query("SELECT * FROM ftree where id=".$this->father);
		$father=mysql_fetch_assoc($ft);
		return new User($father);
	}
	
	function getMother()
	{
		$mt=mysql_query("SELECT * FROM ftree where id=".$this->mother);
		$mother=mysql_fetch_assoc($mt);
		return new User($mother);
	}
	
	function getSpouse()
	{
		$sp=mysql_query("SELECT * FROM ftree where id=".$this->spouse);
		$spouse=mysql_fetch_assoc($sp);
		return new User($spouse);
	}
	
	function getChildren()
	{
		$ch=mysql_query("SELECT * FROM ftree where father=".$this->id." OR mother=".$this->id);
		$children=array();
		$i=0;
		while($child=mysql_fetch_assoc($ch))
			$children[$i++]=new User($child);

		return $children;
	}
	
	function getSiblings()
	{
		$sb=mysql_query("SELECT * FROM ftree where father=".$this->father." AND id!=".$this->id);
		$siblings=array();
		$i=0;
		while($sibling=mysql_fetch_assoc($sb) and $sibling['father']!=-1)
			$siblings[$i++]=new User($sibling);

		return $siblings;
	}
}

function printNode($node)
{
	$spouse=($node->getSpouse()->getId()>-1)?" married ".$node->getSpouse()->getName():"";
	$tbody="
<div id=\"container".$node->getId()."\" class=\"user\">".$node->getName().$spouse.
"<br>
<input type=\"button\" value=\"Add Child\" onclick=\"javascript:addUser(".$node->getId().",'child');\"/>
<input type=\"button\" value=\"Add Spouse\" onclick=\"javascript:addUser(".$node->getId().",'spouse');\"/>
</div>\n";

	if($node->getFather()->getId()>0)
		$tscript="
jsPlumb.connect({source:\"container".$node->getFather()->getId()."\", target:\"container".$node->getId()."\", overlays:overlays});";
	else $tscript="";

	$tnext=$node->getChildren();
	return array("body"=>$tbody,"script"=>$tscript,"next"=>$tnext);
}
?>