# philePaginator
[Phile][] plugin for paginating a set of pages.


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


[Phile]: https://github.com/PhileCMS/Phile
