# Magician

[![Build Status](https://img.shields.io/travis/pushoperations/Magician.svg)](https://travis-ci.org/pushoperations/Magician)
[![Coverage Status](https://img.shields.io/coveralls/pushoperations/Magician.svg)](https://coveralls.io/r/pushoperations/Magician)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/pushoperations/Magician.svg)](https://scrutinizer-ci.com/g/pushoperations/Magician/?branch=master)

[![Total Downloads](https://poser.pugx.org/pushoperations/magician/downloads.svg)](https://packagist.org/packages/pushoperations/Magician)
[![Latest Stable Version](https://poser.pugx.org/pushoperations/magician/v/stable.svg)](https://packagist.org/packages/pushoperations/Magician)
[![Latest Unstable Version](https://poser.pugx.org/pushoperations/magician/v/unstable.svg)](https://packagist.org/packages/pushoperations/Magician)
[![License](https://poser.pugx.org/pushoperations/magician/license.svg)](https://packagist.org/packages/pushoperations/Magician)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/3f2b755a-6ca6-4a85-9b07-43e08ce68310/big.png)](https://insight.sensiolabs.com/projects/3f2b755a-6ca6-4a85-9b07-43e08ce68310)

A library for implementing repositories with magic finders and caching for the Eloquent ORM.

## Contents

- [Installation](#install)
- [Usage](#usage)
- [Examples](#examples)
- [API documentation](http://pushoperations.github.io/Magician)

## Install

The recommended way to install is through [Composer](http://getcomposer.org).

Update your project's composer.json file to include Magic Repository:

```json
{
    "require": {
        "pushoperations/magician": "0.9.*"
    }
}
```

Then update the project dependencies to include this library:

```bash
composer update pushoperations/magician
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

## Usage

A base implementation of the magic repository is already created for use out-of-the-box.

```php
<?php namespace Controllers;

use Controller;
use Magician\Magician;

class ExampleController extends Controller
{
    public function __construct(Magician $magician)
    {
        // Tell this magician instance to be the repository manager for the 'User' model.
        $this->m = $magician->set('Models\User');
    }

    public function create()
    {
        $user = $this->m->firstOrMake(['email' => 'user@example.com']);

        if ($this->m->save($user)) {
            return $user;
        } else {
            return 'error: unable to save the user';
        }
    }

    public function read($id = null)
    {
        if ($id) {
            return $this->m->findById($id);
        } else {
            return $this->m->getById(['>', 0]);
        }
    }

    public function update($id)
    {
        $user = $this->m->findById($id);
        $user->fill([
            'trial' => true,
            'last_login' => new \DateTime,
            'subscription' => '2015',
        ]);

        $user->load('permissions');

        if ($this->rm->save($user)) {
            return $user;
        } else {
            return 'error: unable to save the user';
        }
    }

    public function inactive($date)
    {
        return $this->m->getByLastLogin(['<', $date]);
    }

    public function newTrials()
    {
        return $this->m->get10ByTrial(true, ['subscription' => 'asc'], ['email', 'subscription']);
    }
}
```
