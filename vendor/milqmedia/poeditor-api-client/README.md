# unjudder/poeditor-api-client

Poeditor API Client Module written in PHP.

   `#`          | `Uj\Poed\Api\Client`
----------------|----------
**Version**     | dev-master
**Authors**     | <ul><li>Alrik Zachert <alrik.zachert@unjudder.com></li></ul>
**License**     | [BSD-3-Clause](https://github.com/unjudder/poeditor-php-client/blob/master/LICENSE.md License)

## Overview

API Client Interface for [Poeditor API](https://poeditor.com).

## Installation

Use [Composer](http://getcomposer.org) to install the Poeditor Api Client.
Add the following lines to your `composer.json` file.

```json
   "require": {
       "unjudder/poeditor-api-client": "dev-master"
   }
```

## Try It out

```php

use Uj\Poed\Api\Client;

$apiClient = new Client("YOUR API ACCESS TOKEN");

// list all projects
// returns Uj\Poed\Entity\Project[]
$apiClient->getProjects();

// return a specific project
// returns Uj\Poed\Entity\Project
$project = $apiClient->getProject($projectID);

// add a language to the project
$project->addLanguage('en');

// deletes the given language
$project->deleteLanguage('en');

// list all terms
// returns \Uj\Poed\Entity\Term[]
$project->getTerms();

// list all definitions
// returns \Uj\Poed\Entity\Definition[]
$project->getDefinitions($languageCode);


```


## License

The files in this project are released under the unjudder license.
Please find a copy of this license bundled with this package in the file `LICENSE.md`.
Our License is also available through the web at: http://unjudder.com/license/new-bsd.

