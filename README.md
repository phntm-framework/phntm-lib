# phntm
A lightweight framework designed to feel like magic; blending aspects of laravel and nextjs for the best developer experience.

It's aim is to pull the focus of developement towards the features CUSTOMERS and USERS care about, not what we as developers want, So aspects as routing, templating, and dashboards are all built in, but without the need for you to write any boilerplate code, while providing powerful tools for responsive images, etc at the framework level.

Phntm is based upon MVC principals, but changed to better suit the needs of modern web development, i call this MPE or Model, Page, Endpoint.

## MPE

### Model

Phntm models are built upon doctrine, with inspiration from both symfony and laravel, providing a unified definition in the model class, but using active record for simpler development.
Models also provides the CMS form schema, interpreted from its `Attributes`, which you will learn more about later.

### Page

The page is the view, but simplified in the sense that each endpoint has its own page, this can be manually overridden but by default there should be 1 twig file, and 1 php class for every page page in your application.

### Endpoint

The endpoint is a modified concept of a controller, similar to invokable controllers from laravel, it uses the `Pages\\` namespace to route the URL to the endpoint, and the endpoint is responsible for handling the request, and returning a response.

## Installation

Phntm starter project is coming soon, but for now you would need an nginx server, and a mysql database.

## Routing

Routing within phntm is achieved with PSR-0 namespaces, so the structure of your pages directory will be used to determine the application's routes, this removes the need for dedicated route registration.

the below diagram shows the default directory structure for a new phntm project.
```
pages/
├── Pages/
│   └── Manage.php
└── Slug/
    ├── Manage.php
    ├── Page.php
    └── view.twig
```

The Slug directory is registered as a Dynamic part in Page.php, this allows us to route /foo, /about-us, etc to this endpoint, what is even better is a default value of / is applied, so we can match / and route to this endpoint!

Directories without a Page.php or Manage.php file are not registered as routes, and are not reachable.

The use of Manage.php will be explained later.

## Views

As a Page.php within the pages directory allows that route to become reachable, the view.twig file will be used to render any content for that page. This can optionally be overriden to specify a different template.

## Models and form generation

Phntm comes with some models as part of the framework, such as \Phntm\Lib\Model\Admin and \Phntm\Lib\Model\SimplePage

SimplePage is the model used to represent pages rendered by the /{slug} endpoint, you'll notice in a fresh application that you cannot reach pages such as /foo or /about-us, this is because that endpoint requires a SimplePage record with a matching 'slug' property. so lets create one!

In your browser go to /manage/foo, and login with the username 'admin' and password 'admin', you will then be presented with a form for a SimplePage! if you take a look at the source code for this model, you'll notice that the form schema follows the definition of attributes on the class!

Fill out the form with some dummy data, and then navigate back to /foo, you should then see your page! if you checked the 'Include in navigation?' checkbox you'll see a link to your page in the header, this is thanks to the SimplePageResolver navigation resolver

If you go to /manage/pages you can see a table listing any pages you have created

### Manage.php

If you hadn't figured it out, a Manage.php file at a given location will register a route the same as Page.php, but prepended with /manage, these pages don't require a view.twig as their content is generated based on the class it extends, so far these are Listing, InstanceEdit and Singleton
