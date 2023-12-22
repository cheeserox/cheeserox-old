<?php

namespace openSB\FinaliumApi;

global $auth, $betty;

use Orange\MiscFunctions;
use Orange\NoticeType;

chdir('../../');
$rawOutputRequired = true;
require_once dirname(__DIR__) . '/../../private/class/common.php';
header('Content-Type: application/json');

$post_data = json_decode(file_get_contents('php://input'), true);

$apiOutput = [
    "error" => "Invalid request."
];

if ($auth->getUserBanData()) {
    $apiOutput = [
        "error" => "User is banned!!!"
    ];
}

$database = $betty->getBettyDatabase();

function follow($member): array
{
    global $database, $auth;

    if ($member == $auth->getUserID()) {
        return [
            "error" => "User attempting to follow themself."
        ];
    }

    if ($database->result("SELECT COUNT(user) FROM subscriptions WHERE user=? AND id=?", [$auth->getUserID(), $member]) != 0) {
        $database->query("DELETE FROM subscriptions WHERE user=? AND id=?", [$auth->getUserID(), $member]);
        $result = false;
    } else {
        $database->query("INSERT INTO subscriptions (id, user) VALUES (?,?)", [$member, $auth->getUserID()]);
        $result = true;

        MiscFunctions::NotifyUser($database, $member, 0,0,NoticeType::Follow);
    }

    $number = $database->fetch("SELECT COUNT(user) FROM subscriptions WHERE id = ?", [$member])['COUNT(user)'];

    if ($result) {
        $text = "Unfollow";
    } else {
        $text = "Follow";
    }

    return [
        "followed" => $result,
        "number" => $number,
        "text" => $text,
    ];
}

if (isset($post_data['member'])) {
    if (isset($post_data['action'])) {
        $apiOutput = match ($post_data['action']) {
            'follow' => follow($post_data['member']),
            default => [
                "error" => "Invalid interaction type, or NYI"
            ],
        };
    }
}

echo json_encode($apiOutput);