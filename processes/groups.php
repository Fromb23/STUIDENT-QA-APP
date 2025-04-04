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

if (isset($_GET['group_id']) && isset($_SESSION['user_id'])) {
    $groupId = $_GET['group_id'];
    $userId = $_SESSION['user_id'];

    $conn->begin_transaction();

    try {
        $query_member = "INSERT INTO group_members (user_id, group_id, role, joined_at) VALUES (?, ?, 'member', NOW())";
        $stmt_member = $conn->prepare($query_member);
        if (!$stmt_member) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt_member->bind_param("ii", $userId, $groupId);
        $stmt_member->execute();

        $query_user_group = "INSERT INTO user_groups (user_id, group_id) VALUES (?, ?)";
        $stmt_user_group = $conn->prepare($query_user_group);
        if (!$stmt_user_group) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt_user_group->bind_param("ii", $userId, $groupId);
        $stmt_user_group->execute();

        $conn->commit();
        header("Location: ../public/groups.php?group_id=$groupId");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['leave_group'])) {
        $group_id = $_POST['group_id'];
        $user_id = $_POST['user_id'];

        $conn->begin_transaction();

        try {
            $query = "SELECT role FROM group_members WHERE group_id = ? AND user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $group_id, $user_id);
            $stmt->execute();
            $stmt->bind_result($role);
            $stmt->fetch();
            $stmt->close();

            if ($role == 'admin') {
                $query = "SELECT user_id FROM group_members WHERE group_id = ? AND user_id != ? AND role = 'member' LIMIT 1";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ii", $group_id, $user_id);
                $stmt->execute();
                $stmt->bind_result($new_admin_id);
                $stmt->fetch();
                $stmt->close();

                if ($new_admin_id) {
                    $query = "UPDATE group_members SET role = 'admin' WHERE user_id = ? AND group_id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("ii", $new_admin_id, $group_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            $query_member = "DELETE FROM group_members WHERE group_id = ? AND user_id = ?";
            $stmt_member = $conn->prepare($query_member);
            $stmt_member->bind_param("ii", $group_id, $user_id);
            $stmt_member->execute();
            $stmt_member->close();

            $query_user_group = "DELETE FROM user_groups WHERE group_id = ? AND user_id = ?";
            $stmt_user_group = $conn->prepare($query_user_group);
            $stmt_user_group->bind_param("ii", $group_id, $user_id);
            $stmt_user_group->execute();
            $stmt_user_group->close();

            $query = "SELECT COUNT(*) FROM group_members WHERE group_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $group_id);
            $stmt->execute();
            $stmt->bind_result($member_count);
            $stmt->fetch();
            $stmt->close();

            if ($member_count == 0) {
                $query = "DELETE FROM groups WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $group_id);
                $stmt->execute();
                $stmt->close();
            }

            $conn->commit();

            $_SESSION['message'] = "You've left the group!";
            header("Location: ../public/groups.php");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            echo "Error: " . $e->getMessage();
        }
    }
}
