<?php

// require "connection.php";
require "SMTP.php";
require "PHPMailer.php";
require "Exception.php";


// ENTER THE RELEVANT INFO BELOW
// $mysqlUserName      = "root";
// $mysqlPassword      = "MTrw@20021022";
// $mysqlHostName      = "localhost";
// $DbName             = "kenmedi";
// // $backup_name        = "mybackup.sql";
// $tables             = array("category", "city");

// // or add 5th parameter(array) of specific tables:    array("mytable1","mytable2","mytable3") for multiple tables

// Export_Database($mysqlHostName, $mysqlUserName, $mysqlPassword, $DbName,  $tables, $backup_name);
// // emial();
// function Export_Database($mysqlHostName, $mysqlUserName, $mysqlPassword, $DbName,  $tables = false, $backup_name = false)
// {
//     $mysqli = new mysqli($mysqlHostName, $mysqlUserName, $mysqlPassword, $DbName);
//     $mysqli->select_db($DbName);
//     $mysqli->query("SET NAMES 'utf8'");

//     $queryTables    = $mysqli->query('SHOW TABLES');
//     while ($row = $queryTables->fetch_row()) {
//         $target_tables[] = $row[0];
//     }
//     if ($tables !== false) {
//         $target_tables = array_intersect($target_tables, $tables);
//     }
//     foreach ($target_tables as $table) {
//         $result         =   $mysqli->query('SELECT * FROM ' . $table);
//         $fields_amount  =   $result->field_count;
//         $rows_num = $mysqli->affected_rows;
//         $res            =   $mysqli->query('SHOW CREATE TABLE ' . $table);
//         $TableMLine     =   $res->fetch_row();
//         $content        = (!isset($content) ?  '' : $content) . "\n\n" . $TableMLine[1] . ";\n\n";

//         for ($i = 0, $st_counter = 0; $i < $fields_amount; $i++, $st_counter = 0) {
//             while ($row = $result->fetch_row()) { //when started (and every after 100 command cycle):
//                 if ($st_counter % 100 == 0 || $st_counter == 0) {
//                     $content .= "\nINSERT INTO " . $table . " VALUES";
//                 }
//                 $content .= "\n(";
//                 for ($j = 0; $j < $fields_amount; $j++) {
//                     $row[$j] = str_replace("\n", "\\n", addslashes($row[$j]));
//                     if (isset($row[$j])) {
//                         $content .= '"' . $row[$j] . '"';
//                     } else {
//                         $content .= '""';
//                     }
//                     if ($j < ($fields_amount - 1)) {
//                         $content .= ',';
//                     }
//                 }
//                 $content .= ")";
//                 //every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
//                 if ((($st_counter + 1) % 100 == 0 && $st_counter != 0) || $st_counter + 1 == $rows_num) {
//                     $content .= ";";
//                 } else {
//                     $content .= ",";
//                 }
//                 $st_counter = $st_counter + 1;
//             }
//         }
//         $content .= "\n\n\n";
//     }
//     date_default_timezone_set("Asia/Colombo");
//     $backup_name = $backup_name ? $backup_name : $DbName . "___(" . date('h:i:s') . "_" . date('d-m-Y') . ")__rand" . rand(1, 11111111) . ".sql";
//     // $backup_name = $backup_name ? $backup_name : $DbName.".sql";
//     header('Content-Type: application/octet-stream');
//     header("Content-Transfer-Encoding: Binary");
//     header("Content-disposition: attachment; filename=\"" . $backup_name . "\"");
//     readfile($backup_name);
//     exec('rm ' . $backup_name); 

//     echo $content;
//     echo "success";
//     exit;
// }

// function emial()
// {
//     echo ("hello");
// }
// Include PHPMailer autoloader
// require 'vendor/autoload.php';

// Database configuration
$dbHost = 'localhost';
$dbName = 'vision_concept';
$dbUser = 'root';
$dbPass = 'MTrw@20021022';
// $tables = array("category", "city");

// Create a database connection
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set the backup filename
// $backupFileName = 'database_backup_' . date('Y-m-d_H-i-s') . '.sql';

// Command to create a MySQL database backup
// $command = "mysqldump -u $dbUser -p$dbPass -h $dbHost $dbName > $backupFileName";
// $command = "mysqldump --host=$dbHost --user=$dbUser --password=$dbPass --add-drop-table   --no-create-db=$dbName> chamnikka.sql";
// exec($command);


// Get all tables in the database
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
$tables = array();
$result = $mysqli->query("SHOW TABLES");
while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
}

// Create a backup file
$backupFile = 'backup_' . date('Ymd_His') . '.sql';
$handle = fopen("backup/$backupFile", 'w');

// Loop through each table and export its structure and data
foreach ($tables as $table) {
    
    $structure = $mysqli->query("SHOW CREATE TABLE $table");

    if ($structure === false) {
        die("Error: " . $mysqli->error);
    }

    $structureRow = $structure->fetch_row();

    if ($structureRow === false) {
        die("Error fetching structure row: " . $mysqli->error);
    }

    fwrite($handle, "\n\n" . $structureRow[1] . ";\n\n");

    $data = $mysqli->query("SELECT * FROM $table");

    if ($data === false) {
        die("Error: " . $mysqli->error);
    }

    while ($row = $data->fetch_assoc()) {
        $insert = "INSERT INTO $table VALUES (";
        foreach ($row as $value) {
            $insert .= "'" . $mysqli->real_escape_string($value) . "',";
        }
        $insert = rtrim($insert, ',') . ");\n";
        fwrite($handle, $insert);
    }
}

// Close the file handle
fclose($handle);

// Close the database connection
$conn->close();

// Email configuration
$emailFrom = 'thinuka1@gmail.com';
$emailTo = 'thinuka1@gmail.com';
$emailSubject = 'Database Backup';
$emailMessage = 'Please find the database backup attached.';

// Create a PHPMailer instance
use PHPMailer\PHPMailer\PHPMailer;

// SMTP configuration
$mail = new PHPMailer;
$mail->IsSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'thinuka1@gmail.com';
$mail->Password = 'ucvwpfwrjfmvzhix';
$mail->SMTPSecure = 'ssl';
$mail->Port = 465;
$mail->setFrom($emailTo, 'New Tech');
$mail->addReplyTo($emailTo, 'New Tech');
$mail->addAddress($emailFrom);
$mail->isHTML(true);
$mail->Subject = $emailSubject;
$bodyContent = $emailMessage;
$mail->Body    = $bodyContent;
$rootDir = realpath($_SERVER["DOCUMENT_ROOT"]);
// include "$rootDir/$backupFileName";

$file = "C:/xampp/htdocs/sqlbackup/backup/$backupFile";
$mail->addAttachment($file);

if (!$mail->send()) {
    echo 'Verification code sending failed';
} else {
    echo 'Success';
}
