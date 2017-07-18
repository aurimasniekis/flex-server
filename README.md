# Symfony Flex Server

A proxy-application for [```symfony/flex```](https://github.com/symfony/flex) composer plugin to allow use 3rd party/private recipes.

![Screenshoot](http://i.imgur.com/xC74kAf.png)

## Install

Clone the project

```sh
$ git clone git@github.com:aurimasniekis/flex-server.git
```

Run composer

```sh
$ composer install
```

Clone/Place your recipes inside `/recipes` folder.

```sh
$ git clone git@github.com:symfony/recipes.git ./recipes
```

Run build job to build data files for API

```sh
$ bin/flex build
```

Run the server

```sh
$ php -S 127.0.0.1:8080 routing.php
```

## Usage

Modify your `composer.json` to use ur custom endpoint.

```json
{
	"extra": {
	    "symfony": {
	        "id": "01BNAS3890XQPHCYXQ127296J0",
	        "allow-contrib": false,
	        "endpoint": "http://127.0.0.1:8080",
	    }
    }
}
```

If you want to use a non https connection disable the `secure-http` option :

```json
{
    "config": {
        "secure-http": false
    }
}
