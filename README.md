# ADS Search Client

## Installation

The most efficient way to install ADS Search Client is using Composer:

```
composer require adimeo-data-suite/php-client
```

## Usage

### Initialisation

You must first instantiate an AdsClient object, passing to its constructor some base configuration related to your ADS server:

```php
$searchClient = new AdsClient('http://ads.base.url', 'index.mapping', 'analyzer');
```

This code is sufficient for the client to perform a request on the server, given that the client will grab search context from the query string by default.

The query string parameters expected by the client are:

|Parameter   | Description  |
|---|---|
|**query**   | Elastic style query terms  |
|**from**   | Record index in the resultset   |
|**size**   | Number of records returned (to paginate)  |
|**sort** | Field to sort results on (e.g. field,ASC or field,DESC)|
|**filter**| Results filters (documentation to come)|
|**facetOptions**| Facet options (documentation to come)|
 
 Please note that AdsClient will usually generate required parameters for you. This will be explained later.
 

### Search

To actually search the index, just call the *search()* method:

```php
$result = $searchClient->search();
```

### Adding facets

Facets are one of the most valuable feature of a search engine compared to database search! Adding a facet to your resultset is quite straightforward:

```php
$searchClient->addFacet(new Facet('field.raw'));
```

*Note*: **field** must not be tokenized to be available as facet. If it is, please also keep raw data in the index and refer to *field.raw* instead of *field* (like in the example above) to set your facet up.    

## Redering example

Look at the *example(s)* folder to see actual sample code.

To add a sticky facet, just set the matching flag on the facet:

