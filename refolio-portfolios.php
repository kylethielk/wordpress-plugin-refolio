<?php
require_once WP_PLUGIN_DIR . '/refolio/refolio-portfolio.php';

/**
 * Our model for serialization between javascript/php. This is the object that is stored in
 * Wordpress Options API under 'refolio_portfolios' is.
 */
class Refolio_Portfolios
{
    /**
     * Array of Refolio_Portfolio
     * @var
     */
    public $portfolios;

    /**
     * Sets the data for this object. Recursively calls children classes.
     *
     *
     * @param $data Associative array with our data.
     */
    public function set($data)
    {
        $this->portfolios = array();

        if ($data && $data['portfolios'])
        {
            foreach ($data['portfolios'] as $key => $value)
            {
                $portfolio = new Refolio_Portfolio();
                $portfolio->set($data['portfolios'][$key]);
                $this->portfolios[] = $portfolio;
            }
        }

    }
}
