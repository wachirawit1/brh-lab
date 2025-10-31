<?php
$serverName = "192.168.94.99";
$connectionOptions = [
    "Database" => "BRH2",
    "Uid" => "homc",
    "PWD" => "homc"
];

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn) {
    echo "Connection successful!";
} else {
    die(print_r(sqlsrv_errors(), true));
}
?>
