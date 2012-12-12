# KometPHP

a lightweight RESTFul and HMVC application framework for PHP

[![Build Status](https://secure.travis-ci.org/kometphp/kometphp.png)](http://travis-ci.org/kometphp/kometphp)

## Minimum requirements

* Apache 2.2+
* PHP 5.4.0+

## How to download and install
1. Run this script in the terminal:

```
    git clone https://github.com/kometphp/kometphp.git && cd kometphp && chmod +x bin/install.sh && ./bin/install.sh
```

Or this one to get the development branch

```
    git clone https://github.com/kometphp/kometphp.git && cd kometphp && git checkout develop && chmod +x bin/install.sh && ./bin/install.sh
```

2. Navigate to the project URL, pass all the environment tests and refresh the page

## Standards
* Strict Standards, [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md), [PSR-1](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md) and [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) compatibility
* [Semantic versioning](http://semver.org/)
* Compatible with [composer](https://getcomposer.org/) package distribution system

## Features
* HMVC and RESTFul oriented
* Refactored HMVC engine, so you can extend the framework and write your own router / controller logic.
* Asset management, Flash messages, Logger, Event listener, Profiler ...

## TO-DO:
* Unit testing
* API Documentation and Guide
* Classes for CLI, Auth, ...

## Credits
* To Slim, Kohana, Symfony2 and Fuelphp frameworks for inspiring me, and for some ideas I've picked from them.
