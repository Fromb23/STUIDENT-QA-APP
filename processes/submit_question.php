<?php
session_start();
require dirname(__DIR__) . "/config/db.php";
require dirname(__DIR__) . "/models/Question.php";

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access");
}

$user_id = $_SESSION['user_id'];
$description = trim($_POST['description']);

if (!empty($description)) {
    $question = new Question($conn);

    $success = $question->createQuestion($user_id, $description);

    if ($success) {
        header("Location: ../qa.php");
        exit();
    } else {
        echo "Error: Failed to post question!";
    }
} else {
    echo "Question cannot be empty!";
}
?>