# Muse Core

Muse Core is a lightweight PHP framework for building web applications. It provides a set of tools and utilities to help you build your application quickly and efficiently.

## Features

- Routing: Define routes for your application using a simple and intuitive syntax.
- Error Handling: Handle errors gracefully and provide informative error messages to the user.
- Controllers: Define controllers to handle different parts of your application.
- Database: Connect to a database and perform CRUD operations.
- Session Management: Manage user sessions and store data in the session.

## Getting Started

To get started with Muse Core, follow these steps:

1. Install Muse Core using Composer:

```bash
composer require kaviru/muse-core
```

2. Create a new PHP file and include the `core.php` file:

```php
<?php

require_once 'vendor/autoload.php';

use Core\Core;

Core::run();
```

3. Define routes in the `routes.php` file:

```php
<?php

use Core\Route;

Route::get('/', function () {
    return 'Hello, World!';
});
```

4. Run the application:

```bash
php routes.php
```

## Documentation

For more information on Muse Core, refer to the official documentation at [muse.build](https://muse.build).

## Contributing

Contributions are welcome! If you find a bug or have a suggestion, please open an issue or submit a pull request on the GitHub repository.

## License

Muse Core is licensed under the MIT License. See the [LICENSE](LICENSE) file for more information.