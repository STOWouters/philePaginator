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

- when visiting `/blog`, _all_ pages under the `blog` folder are listed
- when visiting `/blog/post`, _only_ the pages under the `post` subfolder
  are listed
- when visiting `/blog/project`, _only_ the pages under the `project` subfolder
  are listed

And let's do it even more: we want to limit the pages to be listed to 5 posts,
but allow pagination for visitors that want to move back/forth.

_Hell yeah!_ This plugin does that all for you.


## Installation
**Using composer**

    $ composer require 'stijn-flipper/phile-paginator:*' --prefer-dist

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

### Basic usage

1. Create a folder, for example `content/blog/`
2. Put an empty `index.md` (extension depends on your settings, it might be
   `index.textile` as well) in that folder (so you can visit the `/blog/` page)
3. Put all your postings into it (you can put it into a subfolder if you want)

Now you can have in your Twig HTML template:

```twig
<p>You are on {{ paginator.uri }}, which contains {{ paginator.pages|length }} posts:</p>
<ul>
    {% for page in paginator.pages %}
    <li>{{ page.title }}</li>
    {% endfor %}
</ul>

<p>The first page is {{ paginator.first.uri }}, which contains {{ paginator.first.pages|length }} posts:</p>
<p>The last page is {{ paginator.last.uri }}, which contains {{ paginator.last.pages|length }} posts:</p>
<p>The previous page is {{ paginator.previous.uri }}, which contains {{ paginator.previous.pages|length }} posts:</p>
<p>The next page is {{ paginator.next.uri }}, which contains {{ paginator.next.pages|length }} posts:</p>
```

Possible output:

> You are on blog?page=1, which contains 8 posts:
>
> * Hello World!
> * Hello World: The Sequel
> * &hellip;
>
> The first page is blog?page=0, which contains 8 posts:
>
> The last page is blog?page=2, which contains 2 posts:
>
> The previous page is blog?page=0, which contains 8 posts:
>
> The next page is blog?page=2, which contains 2 posts:

To check whether there's a next or previous page in twig, you can use the code
below:

```twig
{{ paginator.previous.pages is empty ? "No previous page!" : "There's still a previous page!" }}
{{ paginator.next.pages is empty ? "No next page!" : "There's still a next page!" }}
```

This is useful if you want to disable the pagination button for example.

I hope that everything is still clear to you, if not, then please post an issue
so I can clarify the `README.md` more, or send a pull request if you think you
could explain it better.


### Custom usage

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
    'posts_per_page' => 8,

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
     * Which number to use as first page.
     *
     * Set this to 1 or any other integer if you wish to set the first page to
     * be 1 instead of 0.
     */
    'first_page' => 0,

    /**
     * How to sort the posts, this actually uses the same syntax as the one
     * from the Phile core.
     */
    'pages_order' => 'meta.date:desc meta.title:asc',

    /**
     * Filter posts.
     *
     * This is an array where each uri is mapped to a function that will be
     * used for determining whether the page belongs to the same set of posts
     * to be paginated.
     *
     * For example:
     *
     *      'blog' => function($page) {
     *          return strpos($page->getUrl(), 'blog/') !== false;
     *      }
     *
     * When visiting the /blog page, all posts that contains a 'blog/' (with
     * trailing slashes) in its uri will be paginated.
     *
     * Check out `lib/Phile/Model/Page.php` for a list of member functions
     * availble for the `$page` argument.
     *
     * By default it paginates all the pages that's in the uri's folder (but
     * the index file). For example the uri: `blog/project/` will paginate all
     * the files under the `blog/project/` folder (recursively), with the
     * exception of `index` files.
     */
    'paginators' => array(
    ),
);
```
