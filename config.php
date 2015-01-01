<?php

return array(
    /**
     * The maximal amount of posts to display in one page.
     *
     * Set to 0 or a negative value if you don't want to limit the posts per
     * page.
     */
    'posts_per_page' => 10,

    /**
     * URL parameter to determine the page offset.
     *
     * For example, with the default URL parameter `page`, the url
     *
     *      /blog?page=3
     *
     * Will (try to) request a page with offset 3.
     */
    'url_parameter' => 'page',

    /**
     * How to sort the posts, this actually uses the same syntax as the one
     * from the Phile core.
     */
    'pages_order' => 'meta.date:desc meta.title:asc',

    /**
     * All the paginators.
     *
     * This is an array where each uri is mapped to a function that will be
     * used for determining whether the page belongs to the same set of posts
     * to be paginated.
     *
     * For example:
     *
     *      '/blog' => function($page) {
     *          return strpos($page->getUrl(), 'blog/');
     *      }
     *
     * When visiting the /blog page, all posts that contains a 'blog/' (with
     * trailing slashes) in its uri will be paginated.
     *
     * Regular expressions are allowed (note that the slashes will be escaped).
     *
     * Optionally, you can consult `lib/Phile/Model/Page.php` for a list of
     * member functions availble for the `$page` argument.
     */
    'paginators' => array(
    ),
);
