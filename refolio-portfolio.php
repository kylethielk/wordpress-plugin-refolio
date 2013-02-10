<?php
require_once 'refolio-portfolio-entry.php';

/**
 * Model class for a portfolio.
 */
class Refolio_Portfolio
{
    /**
     * Unique across all portfolios. No Spaces.
     * @var
     */
    public $id;
    /**
     * Array of Refolio_Portfolio_Entry.
     * @var
     */
    public $entries;

    /**
     * Sets the data for this object. Recursively calls children classes.
     *
     *
     * @param $data Associative array with our data.
     */
    public function set($data)
    {
        $this->id = $data['id'];
        $this->entries = array();

        if ($data && $data['entries'])
        {
            foreach ($data['entries'] as $key => $value)
            {
                $entry = new Refolio_Portfolio_Entry();
                $entry->set($data['entries'][$key]);
                $this->entries[] = $entry;
            }
        }

    }
}
