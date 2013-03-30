<?php
/**
 * Our pages were getting long, moving them to our own class to clean up code.
 */
class Refolio_Pages
{
    /**
     * Builds the review page.
     * @param Refolio_Portfolios $refolio_portfolios
     * @param Refolio $refolio
     */
    public static function review($refolio_portfolios, $refolio)
    {

        ?>
        <div class="wrap">
            <h2>Refolio Portfolios</h2>
            <input type="button" class="button button-large button-primary" value="New Portfolio"
                   class="refolio-new-portfolio-button"

                   onclick="window.location = '<?php echo $refolio->build_admin_modify_page_url(); ?>'"/>
            <table class="widefat fixed refolio-table" style="margin-top: 15px">
                <thead>
                <th scope="col" id="name" class="manage-column">Project ID</th>
                <th scope="col" id="usage" class="manage-column">Shortcode</th>
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
                        <td><strong><?php echo htmlspecialchars($portfolio->id); ?></strong></td>
                        <td>[refolio id="<?php echo htmlspecialchars($portfolio->id); ?>"]</td>
                        <td><?php echo count($portfolio->entries); ?></td>
                        <td>
                            <a href="<?php echo $refolio->build_admin_modify_page_url() . '&refolio_id=' . urlencode($portfolio->id); ?>">Edit</a>
                            | <a
                                href="<?php echo $refolio->build_admin_review_page_url() . '&refolio_del=' . urlencode($portfolio->id); ?>"
                                class="refolio-descructive">Delete</a></td>
                    </tr>
                <?php

                } ?>
                </tbody>

            </table>

            <?php
            if (count($refolio_portfolios->portfolios) < 1)
            {
                ?>
                <p>You currently don't have any portfolios created. <a
                        href="<?php echo $refolio->build_admin_modify_page_url(); ?>">Click here to create your first
                        portfolio.
                </p>
            <?php
            }

            ?>
        </div>
    <?php
    }

    /**
     * Replaces new line characters with <br />
     * @param $string
     * @return String
     */
    public static function nl2br_replace($string)
    {
        $string = str_replace(array("\r\n", "\r", "\n"), "<br />", $string);
        return $string;
    }

    /**
     * Builds the shortcode output.
     * @param Refolio_Portfolio $portfolio
     * @param Refolio $refolio
     */
    public static function shortcode($portfolio, $refolio)
    {
        if (!isset($portfolio) || $portfolio->incremented_id < 0)
        {
            return '!!!Unfortunately there was an error retrieving your reFolio portfolio!!!';
        }
        else
        {
            usort($portfolio->entries, array($refolio, 'entry_sort'));

            $div_id = 'reFolio_portfolio_' . $portfolio->incremented_id;

            $content = '<div id="' . $div_id . '" style="height: ' . htmlspecialchars($portfolio->height) . 'px"></div>';

            $content .= '<script type="text/javascript">jQuery(document).ready(function ()
                {
                    var portfolio = jQuery("#' . $div_id . '").refolio({
            width:' . htmlspecialchars($portfolio->width) . ',
            styleContainer: ' . $portfolio->style_container . ',
            items:[';

            foreach ($portfolio->entries as $key => $entry)
            {

                $content .= '
                    {
                        image:"' . $entry->image . '",
                        title:"' . htmlspecialchars(addslashes($entry->title)) . '",
                        tags:' . $entry->build_tag_array_string() . ',
                        description:"' . htmlspecialchars(addslashes(Refolio_Pages::nl2br_replace($entry->description))) . '",
                        link:"' . htmlspecialchars($entry->url) . '"
                    },';
            }
            //Remove last comma
            $content = substr($content, 0, -1);
            $content .= ']       });    });</script>';
            return $content;
        }
    }


    /**
     * Builds the help page.
     * @param Refolio $refolio
     */
    public static function help($refolio)
    {
        ?>
        <div class="wrap">
            <h2>Refolio Help</h2>

            <p style="width: 600px">Thank you for using reFolio! reFolio is a beautiful and simplistic portfolio
                management
                tool that lets your work
                rather than the software running your portfolio shine. reFolio lets you create an unlimited number of
                portfolios
                that can be displayed anywhere on your wordpress installation.</p>

            <img src="<?php echo plugins_url() . '/refolio/image/help_screenshot.jpg'; ?>"/>

            <h3>Creating Portfolio</h3>

            <p>Start by <a href="<?php echo $refolio->build_admin_modify_page_url(); ?>">creating a portfolio</a>.</p>
            <br/>
            <img src="<?php echo plugins_url() . '/refolio/image/help_create.jpg'; ?>"/>

            <p>Each portfolio must have an ID that is unique across all of your portfolios and must not contain any
                spaces. Each portfolio must also have a defined size. The default size of 700x500px is often perfect for
                most default wordpress installs. You can also optionally let reFolio style the container of the
                portfolio
                for you. This is totally up to you and by default reFolio will not style the container for your
                portfolio.</p>

            <p>Each portfolio must also have at least one entry. A portfolio can have an unlimited number of
                entries.</p>

            <p>Each entry can have the following fields:</p>

            <p style="margin-left: 25px">
                <strong>Name</strong> - Required. The name of this entry.<br/>
                <strong>Description</strong> - Required. Detailed description of your entry.<br/>
                <strong>Image</strong> - Required. An image that represents this entry.<br/>
                <strong>Tags</strong> - Optional. A comma separated list of tags describing the entry i.e "CSS, HTML5,
                Javascript".<br/>
                <strong>URL</strong> - Optional. A link to the entry.<br/>
            </p>

            <p>You can add a new entry by clicking the "Add Portfolio Entry" button.</p>
            <img src="<?php echo plugins_url() . '/refolio/image/help_add_entry.jpg'; ?>"/>

            <p>You can remove an entry by clicking the "Remove Entry" button.</p>
            <img src="<?php echo plugins_url() . '/refolio/image/help_remove_entry.jpg'; ?>"/>

            <p>Entries can be re-ordered by dragging/dropping them.</p>

            <p>When happy with your portfolio click the "Save Portfolio" button at the bottom of the screen.</p>
            <img src="<?php echo plugins_url() . '/refolio/image/help_save.jpg'; ?>"/>

            <br/>

            <h3>Using Your Portfolio</h3>

            <p>Once you have created a portfolio you can use it on any wordpress page/post with the following
                shortcode: </p>

            <p><strong>[refolio id="portfolioId"]</strong></p>

            <p><i>*portfolioId should be the id that you gave your portfolio.</i></p>

            <br/>

            <h3>Reviewing Your Portfolios</h3>

            <p>You can review/modify/delete all of your portfolios at any time by visiting the <a
                    href="<?php echo $refolio->build_admin_review_page_url(); ?>">refolio settings page</a>.</p>

        </div>
    <?php
    }

    /**
     * Builds the modify page.
     * @param Refolio_Portfolio $portfolio
     * @param bool $id_not_unique Whether the supplied portfolio has a unique id, used to show error message.
     */
    public static function modify($portfolio, $id_not_unique)
    {
        ?>
        <div class="wrap">
            <h2><?php echo (empty($portfolio->id) ? "Add New Portfolio" : "Modify Portfolio"); ?></h2>
            <!-- Create the form that will be used to render our options -->
            <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" id="portfolio_form">
                <input type="hidden" id="refolio_action" name="refolio_action" value="new"/>
                <input type="hidden" id="refolio_portfolio" name="refolio_portfolio" value=""/>
            </form>

            <div class="postbox refolio-form" style="margin-top: 15px">
                <div class="handlediv" title="Click to toggle"><br>
                </div>
                <h3 class="refolio-entry-title">Portfolio Details</h3>

                <?php if ($id_not_unique)
                {
                    ?>

                    <div id="refolio_errors_top" class="refolio-errors refolio-errors-top">
                        The ID you entered is not unique. Please enter a new ID and try again.
                    </div>
                <?php
                }
                ?>
                <table class="form-table">
                    <tbody>
                    <tr valign="top">
                        <th scope="row"><strong>ID</strong> <span class="refolio-required">*</span>
                            <br/>
                            <label for="portfolio_id">No Spaces, Letters and Numbers Only.</label>
                        </th>
                        <td>
                            <input type="text" id="portfolio_id" name="portfolio_id"
                                   value="<?php echo htmlspecialchars($portfolio->id); ?>"
                                   class="refolio-form-input"/>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><strong>Size</strong> <span class="refolio-required">*</span>
                            <br/>
                            <label for="portfolio_height">Size of portfolio in pixels.</label>
                        </th>
                        <td>
                            <input type="text" id="portfolio_width" name="portfolio_width"
                                   value="<?php echo htmlspecialchars($portfolio->width); ?>"
                                   class="refolio-small-input"/> by
                            <input type="text" id="portfolio_height" name="portfolio_height"
                                   value="<?php echo htmlspecialchars($portfolio->height); ?>"
                                   class="refolio-small-input"/>px
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><strong>Style</strong> <span class="refolio-required">*</span>
                            <br/>
                            <label for="portfolio_height">Style the container, generally leave as false.</label>
                        </th>
                        <td>
                            <input type="radio" name="portfolio_style"
                                   value="false" <?php echo ($portfolio->style_container == 'false' ? ' checked' : ''); ?>>False
                            &nbsp; &nbsp;
                            <input type="radio" name="portfolio_style"
                                   value="true" <?php echo ($portfolio->style_container == 'true' ? ' checked' : ''); ?>>True
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

            <input type="button" class="button button-large" value="Add Portfolio Entry"
                   style="margin: 10px; float: right"
                   onclick="refolio.admin.portfolio.addEntry();"/>


            <input type="button" class="button button-primary button-large" value="Save Portfolio"
                   onclick="refolio.admin.portfolio.submit();"/>

            <div id="refolio_errors" style="display: none" class="refolio-errors">There are errors, please correct them
                and
                try
                again.
            </div>

            <script type="text/html" id="refolio_entry_template">
                <li id="refolio_entry_<%= id %>" class="postbox " data-entry-id="<%= id %>">
                    <a name="entry_a_<%= id %>"></a>

                    <div class="handlediv" title="Click to toggle"
                         onclick="refolio.admin.portfolio.toggleElementShowHide(<%= id %>);"><br>
                    </div>
                    <h3 class="refolio-entry-title"><span
                            id="portfolio_entry_title_<%= id %>"><%= title ? title : "Portfolio Item" %></span></h3>

                    <div class="inside refolio-form" id="portfolio_entry_inside_<%= id %>">
                        <table class="form-table">
                            <tbody>
                            <tr valign="top">
                                <th scope="row"><strong>Name</strong> <span class="refolio-required">*</span>
                                    <br/>
                                    <label for="entry_title_<%= id %>">Title of portfolio item</label>
                                </th>
                                <td>
                                    <input type="text" id="entry_title_<%= id %>" name="entry_title_<%= id %>"
                                           value="<%= title %>"
                                           onkeyup="refolio.admin.portfolio.itemTitleChange(<%= id %>);"
                                           class="refolio-form-input"/>

                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><strong>Description</strong><span class="refolio-required">*</span>
                                    <br/>
                                    <label for="entry_description_<%= id %>">Brief description of your work</label>
                                </th>
                                <td>
                                    <textarea id="entry_description_<%= id %>"
                                              name="entry_description_<%= id %>"><%=description%></textarea>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><strong>Image</strong> <span class="refolio-required">*</span>
                                    <br/>
                                    <label for="entry_image_<%= id %>">Image for portfolio</label>
                                </th>
                                <td>
                                    <img src="<%= image %>" border="0" id="entry_image_<%= id %>" style="width: 100px"/>
                                    <br/>
                                    <input id="entry_image_button_<%= id %>" type="button"
                                           class="button button-secondary"
                                           value="Select Image"
                                           onclick="refolio.admin.portfolio.launchMediaLibrary(<%= id %>);"
                                           class="refolio-form-input"/>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><strong>Tags</strong>
                                    <br/>
                                    <label for="entry_tags_<%= id %>">Comma separated list of tags</label>
                                </th>
                                <td>
                                    <input type="text" id="entry_tags_<%= id %>" name="entry_tags_<%= id %>"
                                           value="<%= tags %>"
                                           class="refolio-form-input"/>

                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><strong>URL</strong>
                                    <br/>
                                    <label for="entry_url_<%= id %>">URL of your project</label>
                                </th>
                                <td>
                                    <input type="text" id="entry_url_<%= id %>" name="entry_url_<%= id %>"
                                           value="<%= url %>"
                                           class="refolio-form-input"/>

                                </td>
                            </tr>

                            </tbody>
                        </table>


                        <div style="height: 35px">
                            <input type="button" class="button button-secondary" value="Remove Entry"
                                   style="float: right; "
                                   onclick="refolio.admin.portfolio.removeEntry(<%= id %>);"/>
                        </div>
                    </div>
                </li>
            </script>


            <script type="text/javascript">
                jQuery(document).ready(function ()
                {
                    refolio.admin.portfolio.init('<?php echo addslashes(json_encode($portfolio)); ?>', '<?php echo plugins_url() . '/refolio/image/no-image.jpg'; ?>');
                });
            </script>


        </div>

    <?php

    }
}

?>