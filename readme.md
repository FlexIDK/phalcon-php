# Partial Phalcon class rewrite from Zephir lang to PHP

Based on cphalcon 5.x version

## Done

| \Phalcon\ class                             | One23\PhalconPhp\ class                                 |
|---------------------------------------------|---------------------------------------------------------|
| \Phalcon\Encryption\Crypt                   | \One23\PhalconPhp\Encryption\Crypt                      |
| \Phalcon\Encryption\Crypt\PadFactory        | \One23\PhalconPhp\Encryption\Crypt\PadFactory           |
| \Phalcon\Encryption\Crypt\Padding\*         | \One23\PhalconPhp\Encryption\Crypt\Padding\*            |
| \Phalcon\Encryption\Security                | \One23\PhalconPhp\Encryption\Security                   |
| \Phalcon\Encryption\Security\Random         | \One23\PhalconPhp\Encryption\Security\Random            |
| \Phalcon\Autoload\Loader                    | \One23\PhalconPhp\Autoload\Loader                       |
| \Phalcon\Support\Version                    | \One23\PhalconPhp\Support\Version                       |
| \Phalcon\Support\Collection                 | \One23\PhalconPhp\Support\Collection                    |
| \Phalcon\Support\Collection\ReadOnlyCollection | \One23\PhalconPhp\Support\Collection\ReadOnlyCollection |
| \Phalcon\Config\Config                      | \One23\PhalconPhp\Config\Config                         |
| \Phalcon\Domain\PayloadPayload              | \One23\PhalconPhp\Domain\PayloadPayload                 |
| \Phalcon\Domain\Status                      | \One23\PhalconPhp\Domain\Status                         |
| \Phalcon\Domain\PayloadFactory              | \One23\PhalconPhp\Domain\PayloadFactory                 |
| \Phalcon\Annotations\Reflection                       | \One23\PhalconPhp\Annotations\Reflection                |
| \Phalcon\Annotations\Collection                       | \One23\PhalconPhp\Annotations\Collection                |
| \Phalcon\Annotations\AnnotationsFactory                   | \One23\PhalconPhp\Annotations\AnnotationsFactory            |
| \Phalcon\Annotations\Annotation                       | \One23\PhalconPhp\Annotations\Annotation                |

## Draft

- \One23\PhalconPhp\Annotations\Reader

## Todo

- phannot_internal_parse_annotations
- \Phalcon\Annotations\Adapter\Stream

...

## Zephir source code

- https://github.com/phalcon/cphalcon/

## Security

If you discover any security related issues, please email eugene@krivoruchko.info instead of using the issue tracker.

## License

[MIT](https://github.com/FlexIDK/phalcon-php/blob/master/LICENSE)
