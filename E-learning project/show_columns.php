<?php
include "config.php";
$r = $conn->query("DESCRIBE feedback");
while($row = $r->fetch_assoc()){
    echo $row['Field'] . "\n";
}
