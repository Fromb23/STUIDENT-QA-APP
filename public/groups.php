<?php
session_start();
require_once '../config/db.php';
require_once '../models/Groups.php';

$groupsModel = new Groups($conn);

$group_id = $_GET['group_id'] ?? null;
$group = null;
$members = [];
$messages = [];
$groups = $groupsModel->getAllGroups();

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
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Groups</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="vendor/jquery/jquery-3.2.1.min.js"></script>
    <script src="vendor/emoji-picker/lib/js/config.js"></script>
    <script src="vendor/emoji-picker/lib/js/util.js"></script>
    <script src="vendor/emoji-picker/lib/js/jquery.emojiarea.js"></script>
    <script src="vendor/emoji-picker/lib/js/emoji-picker.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="bg-gray-100 p-6">

    <?php if ($group_id && $group): ?>
        <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow">
            <h2 class="text-2xl font-semibold text-gray-700"><?php echo htmlspecialchars($group['name']); ?></h2>
            <p id="descriptionText" class="text-gray-600 cursor-pointer p-2">
                About: <?php echo htmlspecialchars($group['description'] ?: 'No description available.'); ?>
            </p>

            <div id="groupData" data-group-id="<?php echo $group['id']; ?>" style="display: none;"></div>

            <div id="descriptionEditor" class="mt-4" style="display: none;">
                <textarea id="descriptionInput" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500" rows="4" placeholder="Edit group description..."><?php echo htmlspecialchars($group['description']); ?></textarea>
                <button id="updateDescriptionButton" onclick="updateDescription()" class="w-full bg-blue-500 text-white mt-2 py-2 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">Update Description</button>
            </div>

            <h3 class="text-xl font-semibold mt-4">Group Members</h3>

            <?php
            $current_user = $_SESSION['username'];
            $is_current_user = false;
            ?>

            <ul class="mb-4">
                <?php foreach ($members as $member): ?>
                    <?php if ($member['username'] === $current_user): ?>
                        <li class="border p-2 rounded">
                            <?php
                            $joined_at = strtotime($member['joined_at']);
                            $formatted_date = date("F j, Y", $joined_at);
                            ?>
                            You joined this group on <?php echo $formatted_date; ?>
                        </li>
                        <?php $is_current_user = true; ?>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if (!$is_current_user): ?>
                    <p>You are not part of this group.</p>
                <?php endif; ?>
            </ul>

            <h4 class="mt-4">Other Group Members</h4>
            <ul class="mb-4 flex flex-wrap gap-2">
                <?php foreach ($members as $member): ?>
                    <?php if ($member['username'] !== $current_user): ?>
                        <li class="border p-2 rounded">
                            <?php echo htmlspecialchars($member['username']); ?>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>


            <h3 class="text-xl font-semibold"><i class="fas fa-comments mr-2"></i> Messages</h3>
            <div class="border p-3 rounded bg-gray-50 overflow-y-auto space-x-2" style="height: 300px;">
                <?php foreach ($messages as $msg): ?>
                    <?php
                    $sent_at = isset($msg['created_at']) ? strtotime($msg['created_at']) : time();
                    $formatted_time = date("g:i a", $sent_at);
                    $is_me = $msg['username'] === $member['username'];
                    ?>
                    <div class="flex <?php echo $is_me ? 'justify-start' : 'justify-end'; ?> mb-3">
                        <div class="max-w-xs p-2 rounded-lg <?php echo $is_me ? 'bg-blue-100 text-left' : 'bg-blue-100 text-right'; ?>">
                            <strong><?php echo htmlspecialchars($msg['username']); ?>:</strong>
                            <?php echo htmlspecialchars($msg['message']); ?>
                            <p class="text-xs text-gray-500"><?php echo $formatted_time; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <form method="POST" class="mt-4" id="messageForm">
                <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                <div class="flex gap-2">
                    <!-- Message input field -->
                    <textarea name="message" id="message" placeholder="Type a message..." required class="flex-grow px-4 py-2 border rounded-lg focus:ring focus:ring-blue-300" data-emojiable="true" data-emoji-input="unicode"></textarea>

                    <!-- Emoji button -->
                    <button type="button" id="emojiButton" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-smile"></i>
                    </button>

                    <!-- Submit button -->
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>

            <!-- Emoji Picker container -->
            <div id="emojiPicker" class="emoji-picker-container"></div>

            <form method="POST" action="../processes/groups.php" class="mt-4">
                <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                <button type="submit" name="leave_group" class="w-full bg-red-500 text-white mt-2 py-2 rounded-lg hover:bg-red-700">Leave Group</button>
            </form>

            <?php if ($group['created_by_name'] == $_SESSION['user_id'] || $user_role === 'admin'): ?>
                <h3 class="text-xl font-semibold mt-4">Admin Actions</h3>
                <form method="POST" action="../processes/groups.php?group_id=<?php echo $group_id; ?>">
                    <input type="text" name="rename_group" placeholder="Rename Group" required class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-blue-300">
                    <button type="submit" class="w-full bg-green-500 text-white mt-2 py-2 rounded-lg hover:bg-green-700">Rename</button>
                </form>
                <a href="../processes/groups.php?delete_group=<?php echo $group_id; ?>" class="block text-center bg-red-500 text-white mt-2 py-2 rounded-lg hover:bg-red-700">Delete Group</a>
            <?php endif; ?>

        </div>
    <?php else: ?>
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
    <script src="../js/groups.js" defer></script>
</body>

</html>