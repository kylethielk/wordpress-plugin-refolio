<?php
/**
 * Model for each entry in a portfolio.
 */
class Refolio_Portfolio_Entry
{
    public $title;
    public $description;
    /**
     * The URL of the image.
     * @var
     */
    public $image;
    public $url;
    /**
     * String of tags separated by commas.
     * @var
     */
    public $tags;
    /**
     * Integer. The numerical display order.
     * @var
     */
    public $order;

    /**
     * Sets the data for this object.
     *
     *
     * @param $data Associative array with our data.
     */
    public function set($data)
    {
        if (isset($data))
        {

            $this->title = $data['title'];
            $this->description = $data['description'];
            $this->image = $data['image'];
            $this->url = $data['url'];
            $this->tags = $data['tags'];
            $this->order = $data['order'];
        }

    }

}
