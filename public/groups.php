<?php
session_start();
require_once '../config/db.php';
require_once '../models/Groups.php';

$groupsModel = new Groups($conn);

$group_id = $_GET['group_id'] ?? null;
$group = null;
$members = [];
$messages = [];

if ($group_id) {
    $group = $groupsModel->getGroupById($group_id);
    $members = $groupsModel->getMembers($group_id);
    $messages = $groupsModel->getGroupMessages($group_id);
    $is_admin = $groupsModel->isUserAdmin($group_id, $_SESSION['user_id']);
    if ($is_admin) {
        $user_role = 'admin';
    } else {
        $user_role = 'member';
    }
} else {
    $groups = $groupsModel->getAllGroups();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Groups</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    
<?php if ($group): ?>
    <!-- Group details section -->
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-semibold text-gray-700"><?php echo htmlspecialchars($group['name']); ?></h2>
        <p class="text-gray-600"><?php echo htmlspecialchars($group['description'] ?? 'No description available'); ?></p>
        <p class="text-gray-600">You've joined this group.</p>

        <h3 class="text-xl font-semibold mt-4">Group Members</h3>
        <ul class="mb-4">
            <?php foreach ($members as $member): ?>
                <ul class="mb-4">
    <?php foreach ($members as $member): ?>
        <li class="border p-2 rounded">
            <?php 
            $joined_at = strtotime($member['joined_at']);
            $formatted_date = date("F j, Y", $joined_at); 
            ?>
            <?php echo htmlspecialchars($member['username']) . " (" . $member['role'] . ")"; ?>
            <br>
            <small class="text-gray-500">Joined on <?php echo $formatted_date; ?></small>
        </li>
    <?php endforeach; ?>
        </ul>
            <?php endforeach; ?>
        </ul>

        <h3 class="text-xl font-semibold">Messages</h3>
        <div class="border p-3 rounded bg-gray-50 h-70 overflow-y-auto">
            <?php foreach ($messages as $msg): ?>
                <?php 
                if (isset($msg['created_at'])) {
                    $sent_at = strtotime($msg['created_at']);
                    $formatted_time = date("g:i a", $sent_at);
                } else {
                    $formatted_time = "Unknown time";
                }
                ?>
                <p>
                    <strong><?php echo htmlspecialchars($msg['username']); ?>:</strong> 
                    <?php echo htmlspecialchars($msg['message']); ?>
                    <br>
                    <small class="text-gray-500"><?php echo $formatted_time; ?></small>
                 </p>
            <?php endforeach; ?>
        </div>

        <form method="POST" action="../processes/groups.php" class="mt-4">
            <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
            <input type="text" name="message" placeholder="Type a message..." required class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-blue-300">
            <button type="submit" class="w-full bg-blue-500 text-white mt-2 py-2 rounded-lg hover:bg-blue-700">Send</button>
        </form>

        <?php if ($group['created_by_name'] == $_SESSION['user_id'] || $user_role === 'admin'): ?>
            <!-- Admin Actions for Group Creator or Admin -->
            <h3 class="text-xl font-semibold mt-4">Admin Actions</h3>
            <form method="POST" action="../processes/groups.php?group_id=<?php echo $group_id; ?>">
                <input type="text" name="rename_group" placeholder="Rename Group" required class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-blue-300">
                <button type="submit" class="w-full bg-green-500 text-white mt-2 py-2 rounded-lg hover:bg-green-700">Rename</button>
            </form>
            <a href="../processes/groups.php?delete_group=<?php echo $group_id; ?>" class="block text-center bg-red-500 text-white mt-2 py-2 rounded-lg hover:bg-red-700">Delete Group</a>
        <?php endif; ?>

    </div>
<?php else: ?>
    <!-- Available groups section -->
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-semibold">Available Groups</h2>
        <ul>
            <?php foreach ($groups as $group): ?>
                <li><a href="?group_id=<?php echo $group['id']; ?>" class="text-blue-500"><?php echo htmlspecialchars($group['name']); ?></a></li>
            <?php endforeach; ?>
        </ul>

        <h3 class="text-xl font-semibold mt-4">Create a New Group</h3>
        <form method="POST" action="../processes/groups.php">
            <input type="text" name="group_name" placeholder="Group Name" required class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-blue-300">
            <button type="submit" class="w-full bg-blue-500 text-white mt-2 py-2 rounded-lg hover:bg-blue-700">Create Group</button>
        </form>
    </div>
<?php endif; ?>

</body>
</html>