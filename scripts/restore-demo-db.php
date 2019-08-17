<?php

include dirname(__FILE__)."/../src/ChurchCRM/SQLUtils.php";
Use ChurchCRM\SQLUtils;

$sSERVERNAME = "";
$sUSER = "";
$sPASSWORD = "";
$sDATABASE = "";

function extract_config_values($value){

  global $sSERVERNAME,$sUSER,$sPASSWORD,$sDATABASE;

  if (preg_match('/\\$sSERVERNAME\\s+=\\s+[\'"](.*?)[\'"];/',$value,$matches)) {
    $sSERVERNAME = $matches[1];
  }

  if (preg_match('/\\$sUSER\\s+=\\s+[\'"](.*?)[\'"];/',$value,$matches)) {
    $sUSER = $matches[1];
  }

  if (preg_match('/\\$sPASSWORD\\s+=\\s+[\'"](.*?)[\'"];/',$value,$matches)) {
    $sPASSWORD = $matches[1];
  }

  if (preg_match('/\\$sDATABASE\\s+=\\s+[\'"](.*?)[\'"];/',$value,$matches)) {
    $sDATABASE = $matches[1];
  }
}

$config = explode("\n",file_get_contents (dirname(__FILE__)."/../src/Include/Config.php"));
array_map("extract_config_values",$config);

echo "Beginning to restore demo database\n";
echo "MySQL Server: $sSERVERNAME\n";
echo "User: $sUSER\n";
echo "Password: $sPASSWORD\n";
echo "Database: $sDATABASE\n";

$mysqli = new mysqli($sSERVERNAME, $sUSER, $sPASSWORD, $sDATABASE);
$mysqli->select_db($sDATABASE);
echo "Connected to database\n";
echo "Deleting all tables\n";

if ($result = $mysqli->query("SHOW TABLES"))
{
    while($row = $result->fetch_array(MYSQLI_NUM))
    {
        $mysqli->query('DROP TABLE IF EXISTS '.$row[0]);
    }
}
$mysqli->query('SET foreign_key_checks = 1');
echo "Tables deleted, restoring demo db\n";
SQLUtils::sqlImport("demo/ChurchCRM-Database.sql", $mysqli);
echo "Demo db restored\n\n";