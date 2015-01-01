<?php

/**
 * Plugin class.
 */
namespace Phile\Plugin\StijnFlipper\PhilePaginator;

/**
 * Class Plugin
 *
 * @author  Stijn Wouters
 * @link    https://github.com/Stijn-Flipper/philePaginator
 * @license http://choosealicense.com/licenses/mit/
 * @package Phile\Plugin\StijnFlipper\PhilePaginator
 */
class Plugin extends \Phile\Plugin\AbstractPlugin implements \Phile\Gateway\EventObserverInterface
{

    /**
     * The paginator to be used
     *
     * @var function paginator
     */
    private $paginator = null;

    /**
     * The current offset
     *
     * @var int offset
     */
    private $offset = 0;

    /**
     * The requested uri
     *
     * @var string uri
     */
    private $uri = '';

    /**
     * Constructor.
     *
     * Register plugin to Phile Core.
     */
    public function __construct()
    {
        \Phile\Event::registerEvent('request_uri', $this);
    }

    /**
     * Process request.
     *
     * @return void
     */
    public function request($uri)
    {
        // set uri
        $this->uri = $uri;

        // set paginator (set to NULL if there's no such paginator for the
        // requested uri)
        $paginators = $this->settings['paginators'];
        $uri = '/'.$uri;
        $this->paginator = (array_key_exists($uri, $paginators)) ? $paginators[$uri] : null;

        // set page offset
        $key = $this->settings['url_parameter'];
        $this->offset = (array_key_exists($key, $_GET)) ? intval($_GET[$key]) : 0;
    }

    /**
     * Get all the posts to be paginated.
     *
     * @return array The set of posts to be paginated
     */
    public function getPosts()
    {
        // get all the posts
		$repo = new \Phile\Repository\Page($this->settings);
        $pages = $repo->findAll();

        // if there's no paginator provided, then simply return all the pages
        if (null === $this->paginator)
            return $pages;

        // otherwise, use the paginator to determine whether you should
        // paginate the given post
        return array_filter($pages, $this->paginator);
    }

    /**
     * Get all the paginated pages
     *
     * @return array A two dimensional array of posts
     */
    public function getPages()
    {
        // get max posts per page
        $posts_per_page = $this->settings['posts_per_page'];

        // get all the paginated posts
        $posts = $this->getPosts();

        // if max posts per page is less than 1, then simply return all the
        // posts
        if ($posts_per_page < 1)
            return array($posts);

        // otherwise break'em up in chunks of arrays
        return array_chunk($posts, $posts_per_page);
    }

    /**
     * Export template variables variables
     *
     * @return void
     */
    public function export()
    {
        // get template variables
        $registry = 'templateVars';
        $vars = (\Phile\Registry::isRegistered($registry)) ? \Phile\Registry::get($registry) : array();

        // get pages
        $pages = $this->getPages();
        $max_pages = count($pages) - 1;

        // get uri pattern for previous/next navigation
        $uri = $this->uri.'?'.$this->settings['url_parameter'].'=%s';

        // extend template variables
        $vars['paginator'] = array(
            'offset'   => $this->offset,
            'previous' => ($this->offset > 0) ? sprintf($uri, $this->offset - 1) : '',
            'next'     => ($this->offset < $max_pages) ? sprintf($uri, $this->offset + 1) : '',
            'pages'    => $this->getPages(),
        );
        \Phile\Registry::set($registry, $vars);
    }

    /**
     * Execute plugin.
     *
     * @param string $event
     * @param null $data
     *
     * @return void
     */
    public function on($event, $data=null)
    {
        if ('request_uri' === $event)
            $this->request($data['uri']);

        // always export variables
        $this->export();
    }

} // end class
