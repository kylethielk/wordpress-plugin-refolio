<?php
require_once 'refolio-portfolio-entry.php';

/**
 * Model class for a portfolio.
 */
class Refolio_Portfolio
{
    /**
     * Unique across all portfolios. No Spaces. Set by user.
     * @var
     */
    public $id;

    /**
     * Simple incremented ID similar to primary key in DB. Set by us.
     * @var
     */
    public $incremented_id = -1;

    /**
     * Width of the container.
     * @var int
     */
    public $width = 700;
    /**
     * Height of the container.
     * @var int
     */
    public $height = 500;

    /**
     * Whether or not to style the container. Generally this is set to false so we can fit in with
     * the default theme.
     * @var bool
     */
    public $style_container = 'false';

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
        $this->incremented_id = $data['incremented_id'];
        $this->entries = array();
        $this->width = $data['width'];
        $this->height = $data['height'];
        $this->style_container = $data['style_container'];


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
