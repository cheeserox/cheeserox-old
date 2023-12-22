<?php

namespace Orange\Pages;

use Orange\User;
use Orange\OrangeException;
use Orange\Database;

/**
 * Backend code for the MediaWiki-styled version page.
 *
 * @since Orange 1.0
 */
class Version
{
    private $betty;
    private $database;
    public function __construct(\Orange\Orange $betty)
    {
        $this->betty = $betty;
        $this->database = $betty->getBettyDatabase();
    }

    /**
     * Returns an array containing a list of OpenSB authors.
     *
     * @since Orange 1.0
     *
     * @return array
     */
    public function getDevelopers(): array
    {
        return [
            'Bluffingo',
            'icanttellyou',
            'ROllerozxa',
        ];
    }

    /**
     * Returns an array containing the versions of PHP, MySQL and OpenSB.
     *
     * @since Orange 1.0
     *
     * @return array
     */
    public function getVersionData(): array
    {
        return [
            'phpVersion' => [
                'title' => "PHP",
                'info' => phpversion(),
            ],
            'dbVersion' => [
                'title' => "Database software",
                'info' => $this->database->getVersion(),
            ],
            'bettyVersion' => [
                'title' => "OpenSB",
                'info' => $this->betty->getBettyVersion(),
            ],
        ];
    }
}