var refolio = {};
refolio.admin = {};

/**
 * Fluidly scrolls screen to anchor tag with given name.
 * @param {String} anchorName The name of the anchor.
 */
refolio.admin.scrollToAnchor = function (anchorName)
{
    var anchorTag = jQuery("a[name='" + anchorName + "']");
    jQuery('html,body').animate({scrollTop: anchorTag.offset().top}, 'slow');
};

/**
 * Allows us to create/modify a portfolio.
 * @type {*}
 */
refolio.admin.portfolio = (function ($)
{
    /**
     * Will always be set to the id for the NEXT item added.
     * @type {Number}
     * @private
     */
    var _indexCount = 0;
    /**
     * Object representation of our portfolio. Will be populated
     * from JSON passed to us from PHP.
     * @type {null}
     * @private
     */
    var _portfolio = null;

    var _noImageSrc = '';

    /**
     * Initializes the our code.
     * @param {String} portfolioJSON JSON of our portfolio.
     * @param {String} noImageSrc The SRC for the image used when no image has been
     * uploaded yet.
     */
    this.init = function (portfolioJSON, noImageSrc)
    {
        _portfolio = JSON.parse(portfolioJSON);
        _noImageSrc = noImageSrc;

        if (!_portfolio.entries)
        {
            _portfolio.entries = [];
        }

        this.buildEntries();

        /**
         * Called when the user selects an image with the media library.
         * @param html
         */
        window.send_to_editor = function (html)
        {
            var imageUrl = jQuery('img', html).attr('src');
            var currentEntry = refolio.admin.portfolio.getCurrentEntryForMedia();
            currentEntry.image = imageUrl;
            $('#entry_image_' + currentEntry.id).attr('src', imageUrl);

            //Hide popup.
            tb_remove();
        };
    };
    /**
     * Pass to array.sort() to sort by entry.order.
     * @param {Object} entryA Object with order defined.
     * @param {Object} entryB Object with order defined.
     * @return {Number} 1 if entryA.order > entryB.order, -1 if entryB.order > entryA.order, 0 if equal.
     */
    this.sortEntries = function (entryA, entryB)
    {
        if (entryA.order > entryB.order)
        {
            return 1;
        }
        else if (entryA.order < entryB.order)
        {
            return -1;
        }
        return 0;
    };
    /**
     * Builds entries from object form and populates our page with the HTML.
     */
    this.buildEntries = function ()
    {
        if (_portfolio.entries && _portfolio.entries.length > 0)
        {
            _portfolio.entries.sort(this.sortEntries);

            var i = 0;
            for (i; i < _portfolio.entries.length; i++)
            {
                var entry = _portfolio.entries[i];
                entry.id = i;
                entry.order = i;
                $('#refolio_portfolio_entries').append(this.buildEntry(entry, i));
            }
            _indexCount = i;
        }
        else
        {
            //New portfolio, no items exist yet.
            var entry = {id: 0, title: '', description: '', tags: '', url: '', order: 0};
            _portfolio.entries.push(entry);

            $('#refolio_portfolio_entries').append(this.buildEntry(entry, 0));
            _indexCount = 1;
        }

        //Make our items sortable with drag/drop
        var _this = this;

        $('#refolio_portfolio_entries').sortable({
            update: function (event, ui)
            {
                //When we are done with a sort, we need to update our entry.order
                var count = 0;

                $('#refolio_portfolio_entries > li').each(function (index, value)
                {
                    var id = $(this).data('entry-id');

                    //Find portfolio entry for this id
                    var entry = _this.findPortfolioEntry(id);
                    entry.order = count;
                    count++;
                });
            }
        });

    };
    /**
     * Builds jQuery object for entry. Does NOT append to page.
     * @param {*} entry The portfolio entry.
     * @return {*|jQuery|HTMLElement} the jQuery selector for the entries HTML.
     */
    this.buildEntry = function (entry)
    {
        if (!entry.image)
        {
            entry.image = _noImageSrc;
        }

        var item = tmpl($('#refolio_entry_template').html(), this.sanitizeEntryForOutput(entry));


        return $(item);
    };
    /**
     * @param {*} entry The portfolio entry.
     * @return {*} The entry with HTML sanitized.
     */
    this.sanitizeEntryForOutput = function (entry)
    {
        var cleanEntry = {};
        for (var key in entry)
        {
            cleanEntry[key] = this.escapeHtml(entry[key]);
        }
        return cleanEntry;
    };

    this.entityMap = {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': '&quot;',
        "'": '&#39;',
        "/": '&#x2F;'
    };

    this.escapeHtml = function (string)
    {
        var _this = this;
        return String(string).replace(/[&<>"'\/]/g, function (s)
        {
            return _this.entityMap[s];
        });
    };
    /**
     * Find and returns a portfolio entry based on supplied id.
     * @param {Integer} id The id of the entry.
     * @return {*} The entry or null if not found.
     */
    this.findPortfolioEntry = function (id)
    {
        for (var i = 0; i < _portfolio.entries.length; i++)
        {
            if (_portfolio.entries[i].id == id)
            {
                return _portfolio.entries[i];
            }
        }
        return null;
    };

    /**
     * Adds a new entry to the portfolio.
     */
    this.addEntry = function ()
    {
        var entry = {id: _indexCount, order: _indexCount, title: '', description: '', tags: '', url: ''};
        _portfolio.entries.push(entry);

        $('#refolio_portfolio_entries').append(this.buildEntry(entry, 0));

        refolio.admin.scrollToAnchor('entry_a_' + _indexCount);
        ++_indexCount;
    };
    /**
     * Remove an entry from our portfolio with given id.
     * @param {Integer} id ID of entry to remove.
     */
    this.removeEntry = function (id)
    {
        var removeIndex = -1;
        for (var i = 0; i < _portfolio.entries.length; i++)
        {
            var entry = _portfolio.entries[i];
            if (entry.id == id)
            {
                removeIndex = i;
                break;
            }
        }

        if (removeIndex > -1)
        {
            _portfolio.entries.remove(removeIndex);
        }

        //Animate the removal before actually removing.
        $('#refolio_entry_' + id).animate({opacity: 0.0, height: 0, margin: 0}, 1000, 'swing', function ()
        {
            $('#refolio_entry_' + id).remove();

        });


    };

    /**
     * Submit the form to save the portfolio.
     */
    this.submit = function ()
    {
        _portfolio.id = $('#portfolio_id').val();
        _portfolio.width = $('#portfolio_width').val();
        _portfolio.height = $('#portfolio_height').val();

        _portfolio.style_container = $('input[name=portfolio_style]:checked').val();

        var hasError = false;
        if (!_portfolio.id || _portfolio.id.indexOf(' ') >= 0 || _portfolio.id.match(/[^a-z0-9]/gi))
        {
            this.addErrorStyling($('#portfolio_id'));

            hasError = true;
        }
        else
        {
            this.removeErrorStyling($('#portfolio_id'));
            $('#refolio_errors').hide();
        }

        if (!_portfolio.width || _portfolio.width.match(/[^0-9]/gi))
        {
            this.addErrorStyling($('#portfolio_width'));

            hasError = true;
        }
        else
        {
            this.removeErrorStyling($('#portfolio_width'));
            $('#refolio_errors').hide();
        }

        if (!_portfolio.height || _portfolio.height.match(/[^0-9]/gi))
        {
            this.addErrorStyling($('#portfolio_height'));

            hasError = true;
        }
        else
        {
            this.removeErrorStyling($('#portfolio_height'));
            $('#refolio_errors').hide();
        }


        if (_portfolio.entries.length < 1)
        {
            this.addEntry();
        }

        //Update all entries
        for (var i = 0; i < _portfolio.entries.length; i++)
        {
            var entry = _portfolio.entries[i];

            entry.title = $('#entry_title_' + entry.id).val();
            if (!entry.title)
            {
                this.addErrorStyling($('#entry_title_' + entry.id));
                hasError = true;
            }
            else
            {
                this.removeErrorStyling($('#entry_title_' + entry.id));
            }

            entry.description = $('#entry_description_' + entry.id).val();
            if (!entry.description)
            {
                this.addErrorStyling($('#entry_description_' + entry.id));
                hasError = true;
            }
            else
            {
                this.removeErrorStyling($('#entry_description_' + entry.id));
            }

            if (!entry.image || entry.image == _noImageSrc)
            {
                this.addErrorStyling($('#entry_image_button_' + entry.id));
                hasError = true
            }
            else
            {
                this.removeErrorStyling($('#entry_image_button_' + entry.id));
            }

            entry.tags = $('#entry_tags_' + entry.id).val();
            entry.url = $('#entry_url_' + entry.id).val();
        }

        if (hasError)
        {
            $('#refolio_errors').show();
            return;
        }
        else
        {
            $('#refolio_errors').hide();
        }
        var portfolioString = JSON.stringify(_portfolio);
        $('#refolio_portfolio').val(portfolioString);
        $('#portfolio_form').submit();
    };
    /**
     * Adds a red border and light pinkish background to an html element to denote an error.
     * @param {jQuery} jQuerySelector Item to add error styling to.
     */
    this.addErrorStyling = function (jQuerySelector)
    {
        jQuerySelector.css('border', 'solid red 1px');
        jQuerySelector.css('background-color', '#FFF2F2');
    };
    /**
     * Undoes the effects of addErrorStyling.
     * @param {jQuery} jQuerySelector Item to remove error styling from.
     */
    this.removeErrorStyling = function (jQuerySelector)
    {
        jQuerySelector.removeAttr('style');
    };
    /**
     * Show/Hides an element.
     * @param {Integer} id ID of element to hide/show.
     */
    this.toggleElementShowHide = function (id)
    {
        if ($('#portfolio_entry_inside_' + id).is(':visible'))
        {
            $('#portfolio_entry_inside_' + id).hide();
        }
        else
        {
            $('#portfolio_entry_inside_' + id).show();
        }
    };
    /**
     * When the user types to the title field for each entry, we dynamically update the title of that entrie's container.
     * @param {Integer} id The id of the entry whose title is being changed.
     */
    this.itemTitleChange = function (id)
    {
        var newTitle = this.escapeHtml($('#entry_title_' + id).val());
        if (!newTitle)
        {
            newTitle = 'Portfolio Item';
        }
        $('#portfolio_entry_title_' + id).html(newTitle);
    };
    /**
     * When we click upload to launch the Media Library, the media library's callback needs to
     * know which entry to update, this variable stores the id of that entry.
     * @type {Number}
     */
    var currentItemIdForMedia = -1;
    /**
     * When we click upload to launch the Media Library, the media library's callback needs to
     * know which entry to update, this method returns that entry.
     * @return {Object} The entry.
     */
    this.getCurrentEntryForMedia = function ()
    {
        return this.findPortfolioEntry(currentItemIdForMedia);
    };
    /**
     * Launches the media library to select an image.
     * @param {Integer} id ID of entry we are doing this for.
     */
    this.launchMediaLibrary = function (id)
    {
        currentItemIdForMedia = id;

        tb_show('Choose Portfolio Image', 'media-upload.php?type=image&TB_iframe=true');

    };
    return this;

})(jQuery);

/**
 * Taken from John Resig's blog. Adds a remove method to array.
 * @param from
 * @param to
 */
Array.prototype.remove = function (from, to)
{
    var rest = this.slice((to || from) + 1 || this.length);
    this.length = from < 0 ? this.length + from : from;
    return this.push.apply(this, rest);
};

