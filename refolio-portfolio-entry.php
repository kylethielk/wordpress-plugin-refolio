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

    /**
     * Tags string of tags i.e tag1,tag2,tag3 and converts to
     * ["tag1","tag2","tag3"]
     * @return String
     */
    public function build_tag_array_string()
    {
        if (empty($this->tags))
        {
            return '[]';
        }

        $tag_array = explode(',', $this->tags);
        if (count($tag_array) < 1)
        {
            return '[]';
        }

        $tag_string = '[';

        foreach ($tag_array as $key => $tag)
        {
            $tag_string .= '"' . htmlspecialchars(addslashes($tag)) . '",';
        }

        //Strip last comma
        $tag_string = substr($tag_string, 0, -1);

        $tag_string .= ']';

        return $tag_string;

    }

}
