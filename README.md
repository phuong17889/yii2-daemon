# Installation #

## Composer ##

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist phuong17889/yii2-daemon "@dev"
```

or add

```
"phuong17889/yii2-daemon": "@dev"
```

to the require section of your `composer.json` file.

Any command can be converted to daemon

```php
class HelloController extends DaemonController
{
    /**
     * Daemon name
     *
     * @return string
     */
    protected function name(): string
    {
        return 'mail-queue';
    }

    /**
     * Run send mail
     */
    public function worker()
    {
        // Some logic that will be repeateble 
    }
}
```
