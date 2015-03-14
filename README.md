# philePaginator

philePaginator is a [Phile CMS](http://philecms.com/) plugin for paginating a
set of pages or just simply only selects the page you want it to display.

Suppose your content directory looks like this:

    content
    └── blog
        ├── index.textile
        ├── post
        │   ├── ...
        │   └── index.textile
        └── project
            ├── ...
            └── index.textile

With this plugin, you can set the pagination so that:

- when visiting `/blog`, _all_ pages under the `post` and `project` subfolder
  are listed
- when visiting `/blog/post`, _only_ the pages under the `post` subfolder
  are listed
- when visiting `/blog/project`, _only_ the pages under the `project` subfolder
  are listed

And let's do it even more: we want to limit the pages to be listed to 5 posts,
but allow pagination for visitors that want to move back/forth.

_Hell yeah!_ This plugin does that all for you.


## Installation
**Using composer**

    $ composer require 'stijn-flipper/phile-paginator:dev-master' --prefer-dist

**But I don't like composer**

    $ git clone https://github.com/Stijn-Flipper/philePaginator plugins/stijnFlipper/philePaginator

**I don't like git either**

Are you serious?

1. Download the repo's content
2. Put the content into the `plugins/stijnFlipper/philePaginator` directory

Don't forget to enable the plugin by adding the following line to your
`default_config.php` or `config.php`:

```php
$config['plugins']['stijnFlipper\\philePaginator'] = array('active' => true);
```


## Usage

You can fine-tune the behavior by modifying the plugins `config.php`:

```php
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

    ...
);
```

There's also one more configuration you can set: `paginators`, but that's
explained further much below, it's important you understand how this plugin
works before proceeding to using it.

With the default settings, this will sort all the posts out and paginate it
according to `posts_per_page`. Now you can use in your Twig HTML template:

```html
<ul>
    <li>You are on page {{ paginator.offset }} of {{ paginator.pages|length - 1}}</li>
    <li>There are {{ paginator.pages[paginator.offset]|length }} posts on this page</li>
    <li>The url for the next page is {{ paginator.next }}</li>
    <li>The url for the previous page is {{ paginator.previous }}</li>
</ul>

<p>All the posts for this page are:</p>

<ul>
    {% for post in paginator.pages[paginator.offset] %}
    <li>{{ post.title }}</li>
    {% endfor %}
</ul>
```

Or do some other interesting stuffs with the `post` variable: you can display
the content, title, meta,&hellip; just as if it came from the Phile original
`pages` variable (the `pages` variable from Phile is left untouched).

An example output could be:

```html
<ul>
    <li>You are on page 0 of 2</li>
    <li>There are 10 posts on this page</li>
    <li>The url for the next page is blog?page=1</li>
    <li>The url for the previous page is</li>
</ul>

...
```

To check whether there's a next or previous page in twig, you can use the code
below:

    {{ paginator.next is empty ? "No next page!" : "There's still a next page!" }}

This is useful if you want to disable the pagination button for example.

I hope that everything is still clear to you, if not, then please post an issue
so I can clarify the `README.md` more, or send a pull request if you think you
could explain it better.


### Selecting the pages

Now here comes the true power of the plugin: you can tell to the plugin which
post should be paginated and which to discard in pagination. That's where the
`paginators` config key comes in for.

A paginator is simply a function that takes a page as argument and returns a
boolean denoting whether this page should be counted as a post or just discard
it from the pagination.

In the configuration file below, I've added a paginator:

```php
<?php

return array(
    ...

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

        '/blog' => function($page) {
            $template = $page->getMeta()->get('template');
            return (strpos($page->getUrl(), 'blog') !== false && (null === $template || 'post' === $template));
        },

    ),
);
```

When visiting `/blog`, the plugin will use the paginator as implemented in the
`config.php` file: this will paginate the pages that:

- has `blog` in its url AND
- doesn't have a `template` meta OR
- the template meta is exactly `post`

Using the paginators in this way, you can customize what the plugin should
paginate based upon the url the visitor is visiting.

Otherwise, a trivial paginator is used: this will always return true for each
page.


## Demo
How about visiting my website? Yes, I'm using the awesome plugin!

(notice me senpai and the url!)

- <http://hacketyflippy.be/blog>
- <http://hacketyflippy.be/blog?page=0>
- <http://hacketyflippy.be/blog?page=1>
- <http://hacketyflippy.be/blog/project?page=0>
- <http://hacketyflippy.be/blog/post?page=0>
