# ShikimoriAPI
Shikimori API, searching and getting information by ID or name  
Version: 1.0.0  


## Requirements
- PHP >= 7.2
- PHP extensions: cUrl 


## Installation
No complicated installation, just transfer or copy the class file to your projector and install it using compose
```bash
composer require uberchel/ShikimoriAPI
```

## Using
```php
<?php

use uberchel\ShikimoriAPI;

// use composer
require __DIR__ . '/vendor/autoload.php';

// not use composer
require './ShikimoriApi.php';

/* Get the poster. If the size is not specified in the request, the API returns an array with all sizes.
 --sizes [ss, sm, md, lg] - Optional parameters.
 --id - Anime ID
*/
var_dump($shiki->call('getPoster', [
        'size' => 'md', 
        'id' => 58426
    ])
);

/* Search for anime by name
 --list of ratings ['g', 'pg', 'pg_13', 'r', 'r_plus', 'rx']
 --page, limit and rating Optional parameters.
*/
var_dump($shiki->call('searchAnime', [
        'q' => 'Manga Nippon Mukashibanashi',
        'page' => 1,
        'limit' => 3,
        'rating' => 'r'
    ])
);

/* Get all the data about anime
 --id - Anime ID
*/
var_dump($shiki->call('getAnime', [
        'id' => 58426
    ])
);

```

### clone the repository and install the requirements
```bash
git clone https://github.com/uberchel/shikimori-api
```

## LICENSE
The MIT License