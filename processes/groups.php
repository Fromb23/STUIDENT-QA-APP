<?php
session_start();
require_once '../config/db.php';
require_once '../models/Groups.php';

$groupsModel = new Groups($conn);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['message'])) {
        // Post message
        $groupsModel->postMessage(
            $_POST['group_id'],
            $_POST['user_id'],
            $_POST['message']
        );
        header("Location: ../public/groups.php?group_id=" . $_POST['group_id']);
        exit();
    } elseif (isset($_POST['group_name'])) {
        // Create group
        $groupsModel->create(
            $_POST['group_name'],
            $_SESSION['user_id']
        );
        header("Location: ../public/groups.php");
        exit();
    } elseif (isset($_POST['rename_group'])) {
        // Rename group
        $groupsModel->renameGroup(
            $_GET['group_id'],
            $_POST['rename_group']
        );
        header("Location: ../public/groups.php?group_id=" . $_GET['group_id']);
        exit();
    }
}

// Handle delete action
if (isset($_GET['delete_group'])) {
    $groupsModel->deleteGroup($_GET['delete_group']);
    header("Location: ../public/groups.php");
    exit();
}
?>