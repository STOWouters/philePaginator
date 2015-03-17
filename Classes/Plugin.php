<?php

/**
 * Plugin namespace.
 */
namespace Phile\Plugin\StijnFlipper\PhilePaginator;

/**
 * Class Plugin.
 *
 * @author  Stijn Wouters
 * @link    https://github.com/Stijn-Flipper/philePaginator
 * @license http://choosealicense.com/licenses/mit/
 * @package Phile\Plugin\StijnFlipper\PhilePaginator
 */
class Plugin extends \Phile\Plugin\AbstractPlugin implements \Phile\Gateway\EventObserverInterface
{

    /**
     * The query key
     */
    private static $query = '';

    /**
     * Where to start the first page
     */
    private static $begin = 0;

    /**
     * All the (filtered) pages
     */
    private static $pages = array();

    /**
     * The requested uri
     */
    private static $uri = '';


    /**
     * The offset to the pages for this paginator
     */
    private $offset = 0;

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
     * Execute plugin.
     */
    public function on($event, $data=null)
    {
        // update the query to be used to request page
        self::$query = $this->settings['url_parameter'];

        // update the beginning of the page
        self::$begin = $this->settings['first_page'];

        // process requested uri (if triggered)
        if ('request_uri' === $event)
            $this->request($data['uri']);

        // get template variable engine
        $registry = 'templateVars';
        $vars = (\Phile\Registry::isRegistered($registry)) ? \Phile\Registry::get($registry) : array();

        // extend variables
        $vars['paginator'] = $this;

        // export variables
        \Phile\Registry::set($registry, $vars);

        return $this;
    }

    /**
     * Process request.
     */
    public function request($uri)
    {
        // update uri
        self::$uri = urldecode($uri);

        // get page offset
        //
        // note that we're using $_SERVER['QUERY_STRING'] instead the usually
        // $_GET, this is because both seems to work in nginx as for Apache
        // ($_GET fails on some nginx servers with improper rewrite rules)
        $match = array();
        $query_string = array_key_exists('QUERY_STRING', $_SERVER) ? $_SERVER['QUERY_STRING'] : '';
        preg_match('/'.self::$query.'=-?[0-9]+/', $query_string, $match);
        $requested_offset = empty($match) ? self::$begin : intval(substr($match[0], strlen(self::$query.'=')));

        // calculate actual offset and update it
        $this->offset = $requested_offset - self::$begin;

        // filter the pages
        $paginators = $this->settings['paginators'];
        $filter = array_key_exists($uri, $paginators) ? $paginators[$uri] : function($page) {
            return strpos($page->getUrl(), self::$uri) !== false and strpos($page->getFilePath(), 'index') === false;
        };
		$repo = new \Phile\Repository\Page($this->settings);
        $pages = array_filter($repo->findAll(), $filter);

        // chunk'em up if neccessary
        $posts_per_page = $this->settings['posts_per_page'];
        self::$pages = ($posts_per_page <= 0) ? array($pages) : array_chunk($pages, $posts_per_page);

        return $this;
    }

    /**
     * Get complete uri (excluding base url and including query string).
     */
    public function getUri()
    {
        return self::$uri.'?'.self::$query.'='.strval(self::$begin + $this->offset);
    }

    /**
     * Get all the posts on the current page.
     */
    public function getPages()
    {
        if (0 <= $this->offset and $this->offset < count(self::$pages))
            return self::$pages[$this->offset];
        return array();
    }

    /**
     * Get previous paginator
     */
    public function getPrevious()
    {
        $paginator = new \Phile\Plugin\StijnFlipper\PhilePaginator\Plugin();
        $paginator->offset = $this->offset - 1;
        return $paginator;
    }

    /**
     * Get next paginator
     */
    public function getNext()
    {
        $paginator = new \Phile\Plugin\StijnFlipper\PhilePaginator\Plugin();
        $paginator->offset = $this->offset + 1;
        return $paginator;
    }

    /**
     * Get first paginator
     */
    public function getFirst()
    {
        $paginator = new \Phile\Plugin\StijnFlipper\PhilePaginator\Plugin();
        $paginator->offset = self::$begin;
        return $paginator;
    }

    /**
     * Get last paginator
     */
    public function getLast()
    {
        $paginator = new \Phile\Plugin\StijnFlipper\PhilePaginator\Plugin();
        $paginator->offset = count(self::$pages) + self::$begin - 1;
        return $paginator;
    }

} // end class
