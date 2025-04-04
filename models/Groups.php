<?php
class Groups
{
    private $conn;
    private $table = "groups";

    public function __construct($db)
    {
        $this->conn = $db;
    }
    public function updateDescription($group_id, $description)
    {
        $query = "UPDATE groups SET description = ? WHERE id = ?";

        $stmt = $this->conn->prepare($query);

        if ($stmt === false) {
            die('MySQL prepare error: ' . $this->conn->error);
        }

        $stmt->bind_param("si", $description, $group_id);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function createGroup($name, $created_by)
    {
        $query = "INSERT INTO " . $this->table . " (name, created_by, created_at) VALUES (?, ?, NOW())";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            die("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("si", $name, $created_by);
        return $stmt->execute();
    }

    public function getAllGroups()
    {
        $query = "SELECT * FROM " . $this->table;
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getGroupById($id)
    {
        $query = "SELECT g.id, g.name, g.created_at, g.description, u.username as created_by_name 
                  FROM  groups g
                  JOIN users u ON g.created_by = u.id
                  WHERE g.id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }

    public function getMembers($group_id)
    {
        $query = "SELECT u.id, u.username, u.email, gm.role, gm.joined_at 
                  FROM group_members gm
                  JOIN users u ON gm.user_id = u.id 
                  WHERE gm.group_id = ?
                  ORDER BY gm.joined_at DESC";

        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            error_log("Prepare failed: " . $this->conn->error);
            return [];
        }

        $stmt->bind_param("i", $group_id);

        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return [];
        }

        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getGroupMessages($group_id)
    {
        $query = "SELECT m.id, m.message, m.created_at, u.username 
                  FROM group_messages m
                  JOIN users u ON m.user_id = u.id
                  WHERE m.group_id = ?
                  ORDER BY m.created_at ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $group_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            return [];
        }
    }

    // In models/Groups.php
    public function isUserAdmin($group_id, $user_id)
    {
        $query = "SELECT role FROM group_members 
                WHERE group_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $group_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['role'] === 'admin';
        }
        return false;
    }

    // Post a message to group
    public function postMessage($group_id, $user_id, $message)
    {
        $query = "INSERT INTO group_messages (group_id, user_id, message, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            die("Prepare failed: " . $this->conn->error);
        }

        $stmt->bind_param("iis", $group_id, $user_id, $message);
        return $stmt->execute();
    }

    // Rename a group
    public function renameGroup($id, $new_name)
    {
        $query = "UPDATE " . $this->table . " SET name = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $new_name, $id);
        return $stmt->execute();
    }

    // Delete a group
    public function deleteGroup($id)
    {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Add user to group
    public function addMember($group_id, $user_id, $role = 'member')
    {
        $query = "INSERT INTO group_members (group_id, user_id, role) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iis", $group_id, $user_id, $role);
        return $stmt->execute();
    }

    // Remove user from group
    public function removeMember($group_id, $user_id)
    {
        $query = "DELETE FROM group_members WHERE group_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $group_id, $user_id);
        return $stmt->execute();
    }
}
