<?php

function loadInfile($pdo, $file, $table, array $fields) {
    $sql = "INSERT INTO $table (".join(",", $fields).") VALUES (";
    for ($i = 0; $i < sizeof($fields); $i++) {
        if ($i != 0) $sql .= ", ";
        $sql .= "?";
    }
    $sql .= ")";

    $pdo->beginTransaction();
    $stmt = $pdo->prepare($sql);
    $f = fopen($file, "r");
    while (($line = fgets($f)) !== false) {
        $values = explode(";", trim($line));
        $stmt->execute($values);
    }
    $pdo->commit();
}
