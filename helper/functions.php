<?php
include_once('db/Db.php');

function getUserInfo($userId, $conn)
{
    $query = "SELECT * FROM users where id = :id LIMIT 1";
    $statement = $conn->prepare($query);
    $statement->execute(array(
        ':id' => $userId
    ));
    $row_count = $statement->rowCount();
    if ($row_count > 0) {
        return $statement->fetchAll();
    }
}