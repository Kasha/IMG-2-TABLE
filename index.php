<?php
error_reporting(2047);

$stretch = (isset($_POST['stretch']) && intval($_POST['stretch']) > 1) ? intval($_POST['stretch']) : 1;
$prefix = (isset($_POST['prefix']) && preg_match('/[a-z]/i',$_POST['prefix']) == 1) ? $_POST['prefix'] : 't';
$border = FALSE;
if($border && $stretch > 1)
{
$style = 'table{border-right: #000 1px solid;border-top: #000 1px solid}
td{border-left: #000 1px solid;border-bottom: #000 1px solid;}
';
}
else $style = '';
$table = '';

if(isset($_FILES['userfile']))
{
	$backgroundcolor = '';
	$class_array = array();
	$class_count = array();
	$class_table = array();
	for($i=48;$i<58;$i++) array_push($class_table,chr($i));
	for($i=65;$i<91;$i++) array_push($class_table,chr($i));
	for($i=97;$i<123;$i++) array_push($class_table,chr($i));
	$class_table_count = count($class_table);
	for($j=0;$j<$class_table_count;$j++)
	{
		for($i=0;$i<$class_table_count;$i++) array_push($class_table,$class_table[$j].$class_table[$i]);
	}
	
	$uploadfile = $_FILES['userfile']['name'];
	if(move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) 
	{
		$info = getimagesize($uploadfile);
		if(strtoupper($info[2]) == 1 || strtoupper($info[2]) == 3)
		{
			$img = imagecreatefromgif($uploadfile);
			for($y=0;$y<$info[1];$y++)
			{
				$lastcolor = '';
				$colorcounter = 0;
				if($y > 0) $table .= "<tr>";
				for($x=0;$x<$info[0];$x++)
				{
					$currentcolor = imagecolorat($img, $x, $y);
					$colorrgb = imagecolorsforindex($img,$currentcolor);
					$currentcolor = '#'.str_pad(dechex($colorrgb['red']),2,'0',STR_PAD_LEFT).str_pad(dechex($colorrgb['green']),2,'0',STR_PAD_LEFT).str_pad(dechex($colorrgb['blue']),2,'0',STR_PAD_LEFT);
					if(substr($currentcolor,1,1) == substr($currentcolor,2,1) && substr($currentcolor,3,1) == substr($currentcolor,4,1) && substr($currentcolor,5,1) == substr($currentcolor,6,1))
					{
						$currentcolor = '#'.substr($currentcolor,1,1).substr($currentcolor,3,1).substr($currentcolor,5,1);
					}
					if(!isset($class_array[$currentcolor]))
					{
						$classname = $prefix.$class_table[count($class_array)];
						$class_array[$currentcolor] = $classname;
						$style .= ".".$classname."{background:".$currentcolor."}\n";
					}
					
					if($x == 0) $lastcolor = $currentcolor;
					
					if(($y == 0 && $x != 0) || ($colorcounter > 0 && $lastcolor != $currentcolor)) 
					{
						if($colorcounter > 1) $table .= "<td colspan=".$colorcounter." class=".$class_array[$lastcolor].">";
						else $table .= "<td class=".$class_array[$lastcolor].">";
						if(!isset($class_count[$currentcolor])) $class_count[$currentcolor] = 1;
						else $class_count[$currentcolor]++;
						$lastcolor = $currentcolor;
						$colorcounter = 0;
					}
					$colorcounter++;
				}
				if($colorcounter > 1) $table .= "<td colspan=".$colorcounter." class=".$class_array[$currentcolor].">";
				elseif($colorcounter == 1) $table .= "<td class=".$class_array[$currentcolor].">";
			}
			$table .= "</table>";
			arsort($class_count);
			reset($class_count);
			$backgroundcolor = key($class_count);
			$table = "<table cellpadding=0 cellspacing=0 border=0 width=".($info[0] * $stretch)." height=".($info[1] * $stretch)." style=table-layout:fixed bgcolor=".$backgroundcolor."><tr>".str_replace(" class=".$class_array[$backgroundcolor].">",">",$table);
		}
		else echo 'Unsupported file format!';
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>&lt;IMG&gt; 2 &lt;TABLE&gt; Converter</title>
<style type="text/css">
body {font-family: Arial, Helvetica, sans-serif; background-color: #666; color: #FFF;}
table {border: #333333 1px solid;}
<?php echo $style ?></style>
</head>
<body>
<h2>Options</h2>
<form name="bildform" action="index.php" method="post" enctype="multipart/form-data">
	<label>Stretch-factor: <input type="text" name="stretch" value="<?php echo $stretch; ?>" size="1" maxlength="1"></label>&nbsp;
	<label>Class-prefix: <input type="text" name="prefix" value="<?php echo $prefix; ?>" size="1" maxlength="1"></label><br />
	<label>File (GIF/PNG): <input type="file" name="userfile" size="40"></label>
	<input type="submit" value="convert">
</form>

<?php 
if(isset($_FILES['userfile']))
{
	echo '<h2>Result</h2>'.$table.'<br /><h2>Size Comparison</h2>'; 
	echo 'Before: '.round(filesize($uploadfile) / 1024).' KB<br />';
	echo 'After: '.round(strlen($style.$table) / 1024).' KB<br />';
echo '<h2>Code</h2>
<textarea cols="200" rows="100" style="width: 95%"><style type="text/css">
'.$style.'</style>
'.$table.'</textarea>';
}
?>

</body>
</html>
