# wallabag/reimport

A tool to reimport empty entries. 

An empty entry is an entry with an empty body or with '[unable to retrieve full-text content]' inside.

## Using the CLI utility

### Installation

Clone this repository and run `composer install`. You are ready to go !

### Usage

The entry point of the application is the `reimport` file, you can simply type `./reimport`.

There are 2 main commands to know :
- clean [username] : will reimport all entries for a giver user.
- clean-all : will reimport all entries for all users.
__Examples__ :

```
./reimport clean nicosomb
./reimport clean-all
```

## Using it as a low-level library

### Installation

Add this repository to your project's dependencies :

```json
{
    "requires": {
        "wallabag/reimport": "dev-master"
    }
}
```

### Usage

The main class of the library is `Reimport` and provides the functionalities that power the CLI tools.

### Examples

```php
$reimport = new Reimport('nicosomb');
```
