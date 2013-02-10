<?php
/*
Plugin Name: reFolio
Description: A beautiful and elegant portfolio plugin.
Version: 1.0
Author: Kyle Thielk
Author URI: http://www.bitofnothing.com
License: GPL2
*/


class Refolio
{
    function __construct()
    {

        //Register our shortcode
        add_shortcode('refolio', array($this, 'refolio_shortcode'));

        // Register site styles and scripts
        add_action('wp_enqueue_scripts', array($this, 'register_plugin_styles'));
        add_action('wp_enqueue_scripts', array($this, 'register_plugin_scripts'));

        //Register admin
        add_action('admin_menu', array($this, 'admin_plugin_menu'));
        add_action('admin_print_styles', array($this, 'register_admin_styles'));
        add_action('admin_enqueue_scripts', array($this, 'register_admin_scripts'));
    }

    /**
     * Handles the shortcode call.
     * @param $atts
     * @param string $content
     * @return string The HTML.
     */
    function refolio_shortcode($atts, $content = "")
    {
        return "content = $content";
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
        add_menu_page('reFolio Portfolio', 'reFolio', 'manage_options', 'refolio_options', array($this, 'admin_plugin_options'), '', '25.52');
        add_submenu_page('refolio_options', 'New Portfolio', 'New Portfolio', 'manage_options', 'refolio_new', array($this, 'admin_plugin_new'));
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
     * Take an updated/new portfolio and merges it into our master list.
     *
     * This will persist result to DB.
     *
     * @param $portfolio The updated portfolio.
     */
    function mergePortfolio($portfolio)
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
     * The Admin page that allows us to create a new and modify existing portfolio.
     */
    function admin_plugin_new()
    {
        if (!current_user_can('manage_options'))
        {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $this->ensure_root_option_initialized();

        $portfolio = new Refolio_Portfolio();
        if (isset($_POST['refolio_action']) && $_POST['refolio_action'] == 'new')
        {

            $portfolio = new Refolio_Portfolio();
            $portfolio->set(json_decode(stripslashes($_POST['refolio_portfolio']), true));

            //Do nothing if id is empty, this should never happen though as JS should check before submitting
            if (!empty($portfolio->id))
            {
                $this->mergePortfolio($portfolio);

            }
        }


        echo '<div class="wrap">';
        echo '<h2>Add New Portfolio</h2>';
        ?>

    <!-- Create the form that will be used to render our options -->
    <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" id="portfolio_form">
        <input type="hidden" id="refolio_action" name="refolio_action" value="new" />
        <input type="hidden" id="refolio_portfolio" name="refolio_portfolio" value="" />
    </form>

    <div class="postbox refolio-form" style="margin-top: 15px">
        <div class="handlediv" title="Click to toggle"><br>
        </div>
        <h3 class="portfolio-entry-title">Portfolio Details</h3>

        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row"><strong>ID</strong> <span class="refolio-required">*</span>
                    <br/>
                    <label for="portfolio_id">Must be unique, no spaces.</label>
                </th>
                <td>
                    <input type="text" id="portfolio_id" name="portfolio_id" value="<?php echo $portfolio->id; ?>" />
                    <label for="portfolio_id">*Must be unique, no spaces allowed</label>
                </td>
            </tr>
            </tbody>
        </table>

    </div>
    <br/><br/>

    <h3>Portfolio Entries</h3>


    <!--        <div style="width: 100%: overflow: auto;  height: 40px">-->
    <input type="button" class="button button-large" value="Add Portfolio Entry"
           style="margin: -40px 10px 10px 10px; float: right"
           onclick="refolio.admin.portfolio.addEntry();"/>
    <!--        </div>-->
    <ul style="width: 100%" style="list-style: none" id="refolio_portfolio_entries">

    </ul>

    <input type="button" class="button button-large" value="Add Portfolio Entry" style="margin: 10px; float: right"
           onclick="refolio.admin.portfolio.addEntry();"/>


    <input type="button" class="button button-primary button-large" value="Save Portfolio"
           onclick="refolio.admin.portfolio.submit();"/>

    <div id="refolio_errors" style="display: none" class="refolio-errors">There are errors, please correct them and try
        again.
    </div>

    <script type="text/html" id="refolio_entry_template">
        <li id="refolio_entry_<%=id%>" class="postbox " data-entry-id="<%=id%>">
            <a name="entry_a_<%=id%>"></a>

            <div class="handlediv" title="Click to toggle" onclick="refolio.admin.portfolio.toggleElementShowHide(<%=id%>);"><br>
            </div>
            <h3 class="portfolio-entry-title"><span
                    id="portfolio_entry_title_<%=id%>"><%= title ? title : "Portfolio Item" %></span></h3>

            <div class="inside refolio-form" id="portfolio_entry_inside_<%=id%>">
                <table class="form-table">
                    <tbody>
                    <tr valign="top">
                        <th scope="row"><strong>Name</strong> <span class="refolio-required">*</span>
                            <br/>
                            <label for="entry_title_<%=id%>">Title of portfolio item</label>
                        </th>
                        <td>
                            <input type="text" id="entry_title_<%=id%>" name="entry_title_<%=id%>" value="<%=title%>"
                                   onkeyup="refolio.admin.portfolio.itemTitleChange(<%=id%>);" />

                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><strong>Description</strong><span class="refolio-required">*</span>
                            <br/>
                            <label for="entry_description_<%=id%>">Brief description of your work</label>
                        </th>
                        <td>
                            <textarea id="entry_description_<%=id%>"
                                      name="entry_description_<%=id%>"><%=description%></textarea>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><strong>Image</strong> <span class="refolio-required">*</span>
                            <br/>
                            <label for="entry_image_<%=id%>">Image for portfolio</label>
                        </th>
                        <td>
                            <img src="<%=image%>" border="0" id="entry_image_<%=id%>" style="width: 100px"/>
                            <br/>
                            <input id="entry_image_button_<%=id%>" type="button" class="button button-secondary"
                                   value="Select Image"
                                   onclick="refolio.admin.portfolio.launchMediaLibrary(<%=id%>);" />
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><strong>Tags</strong>
                            <br/>
                            <label for="entry_tags_<%=id%>">Comma separated list of tags</label>
                        </th>
                        <td>
                            <input type="text" id="entry_tags_<%=id%>" name="entry_tags_<%=id%>" value="<%=tags%>" />

                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><strong>URL</strong>
                            <br/>
                            <label for="entry_url_<%=id%>">URL of your project</label>
                        </th>
                        <td>
                            <input type="text" id="entry_url_<%=id%>" name="entry_url_<%=id%>" value="<%=url%>" />

                        </td>
                    </tr>

                    </tbody>
                </table>


                <div style="height: 35px">
                    <input type="button" class="button button-secondary" value="Remove Item" style="float: right; "
                           onclick="refolio.admin.portfolio.removeEntry(<%=id%>);" />
                </div>
            </div>
        </li>
    </script>



    <script type="text/javascript">
        jQuery(document).ready(function ()
        {
            refolio.admin.portfolio.init('<?php echo json_encode($portfolio); ?>', '<?php echo WP_PLUGIN_DIR . '/refolio/image/no-image.jpg'; ?>');
        });
    </script>

    <?php
        echo '</div>';
    }

    /**
     * Ensures our master option refolio_portfolios is registered.
     */
    function ensure_root_option_initialized()
    {
        require_once WP_PLUGIN_DIR . '/refolio/refolio-portfolios.php';
        add_option('refolio_portfolios', new Refolio_Portfolios());
    }

    /**
     * The Admin page that allows us to see/modify list of portfolios.
     */
    function admin_plugin_options()
    {
        if (!current_user_can('manage_options'))
        {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $this->ensure_root_option_initialized();

        $refolio_portfolios = get_option('refolio_portfolios');

        ?>
    <div class="wrap">
        <h2>Refolio Portfolios</h2>
        <table class="widefat fixed refolio-table" style="margin-top: 15px">
            <thead>
            <th scope="col" id="name" class="manage-column">Project ID</th>
            <th scope="col" id="entry_count" class="manage-column"># of Items</th>
            <th scope="col" id="actions" class="manage-column">Actions</th>
            </thead>
            <tbody>


                <?php
                for ($i = 0; $i < count($refolio_portfolios->portfolios); $i++)
                {
                    ?>
                <tr>
                    <?php
                    $portfolio = $refolio_portfolios->portfolios[$i];

                    ?>
                    <td><strong><?php echo $portfolio->id; ?></strong></td>
                    <td><?php echo count($portfolio->entries); ?></td>
                    <td><a href="">Edit</a> | <a href="" class="refolio-descructive">Delete</a></td>
                </tr>
                    <?php

                } ?>
            </tbody>

        </table>
    </div>
    <?php
    }


}


$refolio = new Refolio();

?>