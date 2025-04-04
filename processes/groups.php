<?php
session_start();
require_once '../config/db.php';
require_once '../models/Groups.php';

$groupsModel = new Groups($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['message'])) {
        $groupsModel->postMessage(
            $_POST['group_id'],
            $_POST['user_id'],
            $_POST['message']
        );
        header("Location: ../public/groups.php?group_id=" . $_POST['group_id']);
        exit();
    } elseif (isset($_POST['group_name'])) {
        $groupsModel->createGroup(
            $_POST['group_name'],
            $_SESSION['user_id']
        );
        header("Location: ../public/groups.php");
        exit();
    } elseif (isset($_POST['rename_group'])) {
        $groupsModel->renameGroup(
            $_GET['group_id'],
            $_POST['rename_group']
        );
        header("Location: ../public/groups.php?group_id=" . $_GET['group_id']);
        exit();
    }
}

if (isset($_GET['delete_group'])) {
    $groupsModel->deleteGroup($_GET['delete_group']);
    header("Location: ../public/groups.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['group_id']) && isset($_POST['description'])) {
        $group_id = intval($_POST['group_id']);
        $description = trim($_POST['description']);

        $result = $groupsModel->updateDescription($group_id, $description);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Description updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update description.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
