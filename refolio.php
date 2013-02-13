<?php
/*
Plugin Name: reFolio
Description: A beautiful and elegant portfolio plugin.
Version: 1.0
Author: Kyle Thielk
Author URI: http://www.bitofnothing.com
License: GPL2
*/


require_once WP_PLUGIN_DIR . '/refolio/refolio-pages.php';
require_once WP_PLUGIN_DIR . '/refolio/refolio-portfolios.php';

class Refolio
{
    function __construct()
    {


        // Register site styles and scripts
        add_action('wp_print_styles', array($this, 'register_plugin_styles'));
        add_action('wp_enqueue_scripts', array($this, 'register_plugin_scripts'));

        //Register admin
        add_action('admin_menu', array($this, 'admin_plugin_menu'));
        add_action('admin_print_styles', array($this, 'register_admin_styles'));
        add_action('admin_enqueue_scripts', array($this, 'register_admin_scripts'));

        //Register our shortcode
        add_shortcode('refolio', array($this, 'refolio_shortcode'));
    }

    function entry_sort($entryA, $entryB)
    {
        if ($entryA->order > $entryB->order)
        {
            return 1;
        }
        else if ($entryA->order < $entryB->order)
        {
            return -1;
        }
        return 0;
    }

    /**
     * Handles the shortcode call.
     * @param $attributes
     * @param string $content
     * @return string The HTML.
     */
    function refolio_shortcode($attributes, $content = "")
    {

        if (isset($attributes) && isset($attributes['id']))
        {
            $id = $attributes['id'];

            $portfolio = $this->fetch_portfolio($id);

            if (!isset($portfolio) || $portfolio->incremented_id < 0)
            {
                return '!!!Unfortunately there was an error retrieving your reFolio portfolio!!!';
            }
            else
            {
                usort($portfolio->entries, array($this, 'entry_sort'));

                $div_id = 'reFolio_portfolio_' . $portfolio->incremented_id;

                $content = '<div id="' . $div_id . '" style="height: ' . $portfolio->height . 'px"></div>';

                $content .= '<script type="text/javascript">jQuery(document).ready(function ()
                {
                    var portfolio = jQuery("#' . $div_id . '").refolio({
            width:' . $portfolio->width . ',
            styleContainer: ' . $portfolio->style_container . ',
            items:[';

                foreach ($portfolio->entries as $key => $entry)
                {
                    $content .= '
                    {
                        image:"' . $entry->image . '",
                        title:"' . $entry->title . '",
                        tags:["Tag", "Long Tag", "Very Long Tag"],
                        description:"' . $entry->description . '",
                        link:"' . $entry->url . '"
                    },';
                }
                //Remove last comma
                $content = substr($content, 0, -1);
                $content .= ']       });    });</script>';
                return $content;
            }
        }
        return '!!!Unfortunately there was an error retrieving your reFolio portfolio. Please make sure to specify the id i.e [refolio id="portfolioId"]!!!';
    }

    /**
     * Registers scripts for non-admin use of refolio.
     */
    function register_plugin_scripts()
    {
        wp_enqueue_script('refolio_script', plugins_url('js/refolio.min.js', __FILE__), array('jquery'));
    }

    /**
     * Registers styles for non-admin use of refolio.
     */
    function register_plugin_styles()
    {
        wp_enqueue_style('refolio_style', plugins_url('css/refolio.min.css', __FILE__));
    }

    /**
     * Add our admin pages to the sidebar.
     */
    function admin_plugin_menu()
    {
        add_menu_page('reFolio Portfolio', 'reFolio', 'manage_options', 'refolio_review', array($this, 'admin_plugin_review'), '', '25.52');
        add_submenu_page('refolio_review', 'New Portfolio', 'New Portfolio', 'manage_options', 'refolio_modify', array($this, 'admin_plugin_modify'));
        add_submenu_page('refolio_review', 'Help Me!', 'Help Me!', 'manage_options', 'refolio_help', array($this, 'admin_plugin_help'));
    }

    /**
     * Registers and enqueues admin-specific styles.
     */
    public function register_admin_styles()
    {

        wp_enqueue_style('refolio_admin_style', plugins_url('refolio/css/admin.css'));
        wp_enqueue_style('thickbox');

    }

    /**
     * Registers and enqueues admin-specific JavaScript.
     */
    public function register_admin_scripts()
    {

        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('refolio_admin_tmpl_script', plugins_url('refolio/js/tmpl.js'));
        wp_enqueue_script('refolio_admin_script', plugins_url('refolio/js/admin.js'));
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');

    }

    /**
     * Removes the portfolio of the given ID.
     * @param $portfolioId The id of the portfolio to delete.
     */
    function delete_portfolio($portfolioId)
    {
        $refolio_portfolios = get_option('refolio_portfolios');

        if (!is_array($refolio_portfolios->portfolios))
        {
            $refolio_portfolios->portfolios = array();
        }

        $remove_key = null;
        foreach ($refolio_portfolios->portfolios as $key => $value)
        {
            if (strtolower($value->id) == strtolower($portfolioId))
            {
                $remove_key = $key;
                break;
            }
        }

        if (isset($remove_key))
        {
            unset($refolio_portfolios->portfolios[$remove_key]);
            update_option('refolio_portfolios', $refolio_portfolios);
        }

    }

    /**
     * Fetches portfolio of given id.
     * @param $portfolio_id The id of the portfolio to fetch.
     * @return Refolio_Portfolio
     */
    function fetch_portfolio($portfolio_id)
    {
        $refolio_portfolios = get_option('refolio_portfolios');

        if (!is_array($refolio_portfolios->portfolios))
        {
            $refolio_portfolios->portfolios = array();
        }

        foreach ($refolio_portfolios->portfolios as $key => $value)
        {
            if (strtolower($value->id) == strtolower($portfolio_id))
            {
                return $value;
            }
        }

        return new Refolio_Portfolio();

    }

    /**
     * Take an updated/new portfolio and merges it into our master list.
     *
     * This will persist result to DB.
     *
     * @param $portfolio The updated portfolio.
     */
    function merge_portfolio($portfolio)
    {
        $refolio_portfolios = get_option('refolio_portfolios');

        if (!is_array($refolio_portfolios->portfolios))
        {
            $refolio_portfolios->portfolios = array();
        }

        foreach ($refolio_portfolios->portfolios as $key => $value)
        {
            if (strtolower($value->id) == strtolower($portfolio->id))
            {
                $refolio_portfolios->portfolios[$key] = $portfolio;
                update_option('refolio_portfolios', $refolio_portfolios);
                return;
            }
        }

        //If we make it here, this is a new portfolio
        $refolio_portfolios->portfolios[] = $portfolio;
        update_option('refolio_portfolios', $refolio_portfolios);
    }

    /**
     * Builds the help page.
     */
    function admin_plugin_help()
    {
        if (!current_user_can('manage_options'))
        {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        Refolio_Pages::help($this);
    }

    /**
     * Checks if the id for the supplied portfolio is unique.
     * @param $portfolio The porftolio to check.
     * @return bool True if unique, false otherwise.
     */
    function portfolio_id_unique($portfolio)
    {
        $refolio_portfolios = get_option('refolio_portfolios');

        if (!is_array($refolio_portfolios->portfolios))
        {
            $refolio_portfolios->portfolios = array();
        }

        foreach ($refolio_portfolios->portfolios as $key => $value)
        {
            if (strtolower($value->id) == strtolower($portfolio->id) && $value->incremented_id != $portfolio->incremented_id)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculates the next incremented id for new portfolio.
     * @return int the next incremented id.
     */
    function next_incremented_id()
    {
        $next_incremented_id = 0;
        $refolio_portfolios = get_option('refolio_portfolios');

        if (!is_array($refolio_portfolios->portfolios))
        {
            $refolio_portfolios->portfolios = array();
        }

        foreach ($refolio_portfolios->portfolios as $key => $value)
        {
            if ($value->incremented_id >= $next_incremented_id)
            {
                $next_incremented_id = $value->incremented_id + 1;
            }
        }

        return $next_incremented_id;
    }

    /**
     * The Admin page that allows us to create a new and modify existing portfolio.
     */
    function admin_plugin_modify()
    {
        if (!current_user_can('manage_options'))
        {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $this->ensure_root_option_initialized();

        $id_not_unique = false;

        $portfolio = new Refolio_Portfolio();
        if (isset($_POST['refolio_action']) && $_POST['refolio_action'] == 'new')
        {

            $portfolio = new Refolio_Portfolio();
            $portfolio->set(json_decode(stripslashes($_POST['refolio_portfolio']), true));

            //Do nothing if id is empty, this should never happen though as JS should check before submitting
            if (!$this->portfolio_id_unique($portfolio))
            {
                $id_not_unique = true;
            }
            else if (!empty($portfolio->id))
            {
                $this->merge_portfolio($portfolio);
                header('Location: ' . $this->build_admin_review_page_url());
            }
        }
        else if (isset($_GET['refolio_id']))
        {
            $portfolio = $this->fetch_portfolio($_GET['refolio_id']);
        }

        if ($portfolio->incremented_id < 0)
        {
            //We need to set a new incremented id.
            $portfolio->incremented_id = $this->next_incremented_id();
        }

        Refolio_Pages::modify($portfolio, $id_not_unique);
    }

    /**
     * Ensures our master option refolio_portfolios is registered.
     */
    function ensure_root_option_initialized()
    {
        add_option('refolio_portfolios', new Refolio_Portfolios());
    }

    /**
     * Builds URL for page to review portfolios.
     * @return string The URL.
     */
    function build_admin_review_page_url()
    {
        return $_SERVER['PHP_SELF'] . '?page=refolio_review';
    }

    /**
     * Builds URL for page to create/modify new portfolio.
     * @return string The URL.
     */
    function build_admin_modify_page_url()
    {
        return $_SERVER['PHP_SELF'] . '?page=refolio_modify';
    }

    /**
     * The Admin page that allows us to see/modify list of portfolios.
     */
    function admin_plugin_review()
    {
        if (!current_user_can('manage_options'))
        {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $this->ensure_root_option_initialized();


        if (isset($_GET['refolio_del']))
        {
            $delete_id = $_GET['refolio_del'];
            $this->delete_portfolio($delete_id);
        }

        $refolio_portfolios = get_option('refolio_portfolios');

        Refolio_Pages::review($refolio_portfolios, $this);

    }


}


$refolio = new Refolio();

?>