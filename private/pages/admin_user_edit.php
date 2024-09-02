<?php

namespace OpenSB;

global $auth, $twig, $database, $orange, $path;

use SquareBracket\UnorganizedFunctions;
use SquareBracket\UserData;

if (!$auth->isUserAdmin()) {
    UnorganizedFunctions::bannerNotification("You do not have permission to access this page.", "/");
}

if (!$auth->hasUserAuthenticatedAsAnAdmin()) {
    UnorganizedFunctions::bannerNotification("Please login with your admin password.", "/admin/login");
}

if ($orange->getLocalOptions()["skin"] != "biscuit" && $orange->getLocalOptions()["skin"] != "charla") {
    UnorganizedFunctions::bannerNotification("Please change your skin to Biscuit.", "/theme");
}

$username = $path[3] ?? null;

$user = $database->fetch("SELECT * FROM users u WHERE u.name = ?", [$username]);

if (isset($_POST['ban_user'])) {
    // Don't ban non-existent users.
    if (!$database->fetch("SELECT u.name FROM users u WHERE u.name = ?", [$_POST["ban_user"]])) {
        UnorganizedFunctions::bannerNotification("This user does not exist.", "/admin/users/");
    }
    // Don't ban mods/admins.
    if ($database->fetch("SELECT u.powerlevel FROM users u WHERE u.name = ?", [$_POST["ban_user"]])["powerlevel"] != 1) {
        UnorganizedFunctions::bannerNotification("This user cannot be banned.", "/admin/users/");
    }
    // Check if user is already banned, if not, then ban. Otherwise, unban.
    $id = $database->fetch("SELECT u.id FROM users u WHERE u.name = ?", [$_POST["ban_user"]])["id"];
    if ($database->fetch("SELECT b.userid FROM bans b WHERE b.userid = ?", [$id])) {
        $database->query("DELETE FROM bans WHERE userid = ?", [$id]);
        UnorganizedFunctions::bannerNotification("Unbanned " . $_POST["ban_user"] . '.' , "/admin/users", "success");
    } else {
        $database->query("INSERT INTO bans (userid, reason, time) VALUES (?,?,?)",
            [$id, "Banned by " . $auth->getUserData()["name"], time()]);
        UnorganizedFunctions::bannerNotification("Banned " . $_POST["ban_user"] . '.', "/admin/users", "success");
    }
}

if ($user["ip"] != "999.999.999.999") {
    $users_with_matching_ips = $database->fetchArray($database->query("SELECT u.name, u.title FROM users u WHERE u.ip = ? AND id != ?",
        [$user["ip"], $user["id"]]));
} else {
    $users_with_matching_ips = [];
}

$old_username_data = $database->fetchArray($database->query("SELECT * FROM user_old_names WHERE user = ?", [$user["id"]]));

$notes = $database->fetchArray($database->query("SELECT * FROM user_staff_notes WHERE user = ?", [$user["id"]]));

$notes_proper = [];

foreach ($notes as $note) {
    $userData = new UserData($database, $note["author"]);
    $notes_proper[] = [
        "content" => $note["note"],
        "time" => $note["time"],
        "author" => [
            "id" => $note["author"],
            "info" => $userData->getUserArray(),
        ],
    ];
}

echo $twig->render('admin_user_edit.twig', [
    'user' => $user,
    'users_with_matching_ips' => $users_with_matching_ips,
    'notes' => $notes_proper,
    'old_names' => $old_username_data
]);