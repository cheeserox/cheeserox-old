<?php

namespace Orange\Pages;

use Orange\MiscFunctions;
use Orange\User;
use Orange\OrangeException;
use Orange\Database;

/**
 * Backend code for the submission browsing page.
 *
 * @since Orange 1.0
 */
class SubmissionSearch
{
    private \Orange\Database $database;
    private array $submissions;
    private $order;
    private $limit;
    private $submission_count;

    public function __construct(\Orange\Orange $betty, $type, $page, $query)
    {
        global $auth;
        $this->order = $this->getOrderFromType($type);
        $this->limit = sprintf("LIMIT %s,%s", (($page - 1) * 20), 20);

        $whereRatings = MiscFunctions::whereRatings();

        $this->database = $betty->getBettyDatabase();
        $this->submissions = $this->database->fetchArray(
            $this->database->query(
                "SELECT v.* FROM videos v WHERE (v.tags LIKE CONCAT('%', ?, '%') OR v.title LIKE CONCAT('%', ?, '%') OR v.description LIKE CONCAT('%', ?, '%')) AND $whereRatings AND v.video_id NOT IN (SELECT submission FROM takedowns) ORDER BY $this->order DESC $this->limit
                ", [$query, $query, $query]));
    }

    private function getOrderFromType($type): string
    {
        switch ($type) {
            case 'recent':
                $order = "v.time";
                break;
            case 'popular':
                $order = "views";
                break;
            case 'discussed':
                $order = "comments";
                break;
            case 'favorited':
                $order = "favorites";
                break;
            case 'random':
                $order = "RAND()";
                break;
            default:
                $order = "v.time";
                break;
        }
        return $order;
    }

    public function getData(): array
    {
        return [
            "submissions" => MiscFunctions::makeSubmissionArray($this->database, $this->submissions),
        ];
    }
}