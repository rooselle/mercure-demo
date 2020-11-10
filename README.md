# Mercure Demo Project
If you just want to run this project, clone it and run `make project-install` in a terminal.

`http://your-domain/` will display a Demo of the use of Mercure in a small Pizza App.  
`http://your-domain/publishing` & `http://your-domain/subscribing` will display a Demo of the use of topics and private updates in Mercure.
 
# How to use Mercure in a Web App

> Mercure is an open protocol for real-time communications designed to be fast, reliable and battery-efficient. It is a modern and convenient replacement for both the Websocket API and the higher-level libraries and services relying on it.
>
> — [mercure.rocks](https://mercure.rocks)

## How does Mercure work ?

### Too long; didn't read
To put it simply, Mercure has a *hub* which is located at some url : in this project, it's your domain on port 3000 at the hub page (let's say `http://localhost:3000/.well-known/mercure`). When something happens on the server, and you want every client to know about it at the same time without them having to refresh the page, you *publish* data to the hub under a *topic*, to inform what this publication is about. On the client side, you *subscribe* to this topic : whenever there's a publication on the particular topic you subscribe to you'll now be informed, and you can act accordingly (display a notification, for example).

![mercure.rocks diagram of the process](https://mercure.rocks/static/main.png)

## Let's put this into practice
* [Initialize a project](#initialize-a-project)
* [Set up Mercure](#set-up-mercure)
* [Use Mercure](#use-mercure)
  * [Installation](#installation)
  * [Publish with API Platform](#publish-with-api-platform)
  * [Publish with the Mercure Component of Symfony](#publish-with-the-mercure-component-of-symfony)
  * [Publish with Guzzle](#publish-with-guzzle)
  * [Subscribe to topics](#subscribe-to-topics)
* [Send private updates in Mercure (from 0.10 version of Mercure)](#send-private-updates-in-mercure-from-010-version-of-mercure)
  * [What is a private update?](#what-is-a-private-update)
  * [Publish private updates](#publish-private-updates)
  * [Subscribe to topics of a private update](#subscribe-to-topics-of-a-private-update)
* [Use the targets in Mercure (before 0.10 version of Mercure)](#use-the-targets-in-mercure-before-010-version-of-mercure)
  * [What is a target in Mercure?](#what-is-a-target-in-mercure-)
  * [Publish to targets](#publish-to-targets)
  * [Subscribe as a target](#subscribe-as-a-target)

### Initialize a project
You can absolutely take the `docker-compose.yml` file of this project and use it in your own project, Symfony or not Symfony. Or you can start from scratch, as long as you have somewhere to run Mercure.

### Set up Mercure
The following instructions are for a set up with docker. If you want to install Mercure with the binary file, you can [read the official documentation](https://mercure.rocks/docs/getting-started).

Add the following code to your docker-compose :
```json
# .docker/docker-compose.yml

mercure:
  image: dunglas/mercure
  container_name: mercure_container_name
  environment:
      ALLOW_ANONYMOUS: 1
      JWT_KEY: aVerySecretKey # put here whatever secret key you wish
      PUBLISH_ALLOWED_ORIGINS: '*'
      CORS_ALLOWED_ORIGINS: http://localhost # set this to your domain url (without apostrophes !)
  ports:
      - 3000:80
```
It is very important to set properly the `CORS_ALLOWED_ORIGINS` attribute, otherwise the clients won't be able to send their authorization cookie to the Mercure hub.

Don't forget to run your containers :
`docker-compose up -d`

If you've created a Symfony App, install the Symfony Component which implements Mercure :
`composer require mercure`

Then, you need to generate a JWT token that your application must bear to be able to *publish* updates to the Mercure Hub. Go to [jwt.io](https://jwt.io/#debugger-io?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InB1Ymxpc2giOltdfX0.rQB2YPCYz8NX2V1k_a9G3E_AQ6i_1JidlOrOEhUtJaw). The payload should at least contain the following structure :  
```json
{
  "mercure": {
    "publish": []
  }
}
```
This means that the app will only be authorized to publish public updates (ie. it won't be able to publish private updates, but we will see that later). Replace the empty array by `["*"]` to allow the app to publish public _and_ private updates.

Don't forget to set your private key in the "verify signature" panel of the jwt.io form. This key should be the same as the one you put in your docker-compose file (or elsewhere depending on how you installed the hub).

You can now set your environment variables (if you're in a Symfony app) :
```
MERCURE_PUBLISH_URL=http://mercure/.well-known/mercure
# put below the JWT token you've just generated
MERCURE_JWT_TOKEN=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InB1Ymxpc2giOltdfX0.Oo0yg7y4yMa1vr_bziltxuTCqb8JVHKxp-f_FwwOim0
```

As you can see, the `MERCURE_PUBLISH_URL` is `mercure` instead of `localhost:3000` : that is because it must point to the mercure container. At the moment, I cannot make it work with writing `localhost:3000` : if you have the solution, I'm all ears !

Now go to your hub address (`http://localhost:3000/.well-known/mercure`), if you see *Missing "topic" parameter* be displayed : you're all set !

### Use Mercure
You can use Mercure several ways : with or without API Plaftorm, with or without Vue.js... and even with or without Symfony. This demo project (regarding the Pizza App) uses Symfony, API Platform, and Vue.js. As for the demo of the use of targets, it's Symfony only (but could easily be done without framework).

#### Installation
Now you've installed your app and configured the Mercure hub, let's install API Platform and Vue.js (pass this step if you don't want to use them).

Install API Platform (in a Symfony app) :
``
composer require api
``
(Check out the [API Platform documentation](https://api-platform.com/docs/core/getting-started/) for more info on the setting up)

Install Vue.js :
``
npm install vue vue-loader vue-template-compiler
``
(Check out the [Vue.js documentation](https://fr.vuejs.org/v2/guide/index.html) for more info on the setting up)

#### Publish with API Platform
Set up your entities and your front-end app. API Platform does almost all the work for you : you won't have to make any publishing ! For this to work, you have to tell API Platform that it should use Mercure. For that, the ApiResource annotation of your Entity should look like this : `@ApiResource(mercure=true)`.

Now, let's take the example of this project. A user can create, edit, or delete pizzas. All of these crud operations make an api call to `/api/pizzas/{id}`, whether it's with the method POST, PUT, or DELETE. If we update the pizza whose id is 2, API Platform will publish to the hub with the following topic : `http://localhost/api/pizzas/2`. (Note that the first part of the topic url is the domain where your project is located.)

So, if you want to know when the pizza 2 is updated, the url you should subscribe to on the client side is : `http://localhost:3000/hub?topic=http://localhost/api/pizzas/2`. *Yes, but I have a thousand pizzas in my database, and I want to add more...* Well, just subscribe to `http://localhost:3000/hub?topic=http://localhost/api/pizzas/{id}` and Mercure will dynamically change the id so that you subscribe in one time to the publications concerning *all* the pizzas.

#### Publish with the Mercure Component of Symfony
To publish an update without API Platform, but with the help of the Mercure Component of Symfony, put the below code (and adapt it, of course) in your controller.
```php
// App/Controller/PizzaController.php

use Symfony\Component\Mercure\PublisherInterface;
use App\Repository\PizzaRepository;
// ...

class PizzaController extends AbstractController
{
    protected PizzaRepository $pizzaRepository;

    // ...

    public function update(PublisherInterface $publisher): RedirectResponse
        {
            $pizza = $this->pizzaRepository->findById(2);
            $pizza->name = "A new name";
      
            $update = new Update(
                ['http://localhost/api/pizzas/2'],
                json_encode($pizza)
            );
            
            $publisher($update);
    
            return $this->redirectToRoute('home');
        }
}
```

What Symfony is actually doing : it makes an HTTP POST request to the hub of Mercure (which you have previously configured in your .env file), with `http://localhost/api/pizzas/2` as topic, and `json_encode($pizza)` (the modified and encoded pizza) as data.

You can also publish an update with two (or more) topics, by adding a topic in the array of the first argument of the `Update` constructor. Actually if the `Update` only has one topic you don't have to put it in an array (it will be converted into an array in the `Update` constructor), but in the publishing/subscribing demo project, for example, I refactor the Update-sending into a method and I prefer to type the topic variable as an array.

If you want to see a concrete example, go to the `PublishingController.php` (in the *src/Controller* folder) of this project.

#### Publish with Guzzle
As said just before, Symfony just made an HTTP POST request to the hub of Mercure, it means anyone can make an HTTP POST request, even if they're not using the Mercure Component, and even if it's not a framework. To publish an update with Guzzle, copy/paste (and adapt) the below code.

```php
// MyClass.php

use GuzzleHttp\Client;
// ... 

public function create(): void
{
    $pizza = json_encode(new Pizza('Quatre fromages'));

    $client = new Client(['base_uri' => 'http://localhost:3000']);
    $client->request('POST', '/.well-known/mercure', [
        'headers' => [
            'Authorization' => 'Bearer my.superlong.jwt',
        ],
        'form_params' => [
            'topic' => 'http://localhost/api/pizzas/2',
            'data' => $pizza
        ]
    ]);
}
```

#### Subscribe to topics
No matter how you publish updates, the subscribing part is the same. To subscribe to topics on your front-end app, it's as simple and as short as that :

```javascript
// script.js

subscribe() {
  const hubUrl = 'http://localhost:3000/.well-known/mercure';
  const eventSource = new EventSource(`${hubUrl}?topic=http://localhost/api/pizzas/{id}`);
  eventSource.onmessage = event => {
    const pizza = (JSON).parse(event.data);
    console.log(pizza);
  }
}
```

Actually, when you'll implement it on your project, you will do things a little differently. Indeed, the [specification](https://mercure.rocks/spec) of Mercure indicates that the hub URL should be discoverable, which implies that on the client side, the hub URL should be discovered rather than hard-coded. Thus, you'll have to set a `Link` header with the hub URL (`<http://localhost:3000/.well-known/mercure>; rel="mercure"`) in the response of a GET request when a resource is fetched. If you use API Platform, you don't have to do anything on your app, it sends the header for you.  
NB : At the moment, I cannot find a way to use the autodiscovery mechanism with API Platform when Mercure is installed with docker, because API Platform uses the hub url written in the `.env` file and not the actual one. Thus, the hub url is hard-coded in the demo project.

```javascript
// assets/js/view/Pizzas.vue

getPizzas() {
    axios
      .get('http://localhost/api/pizzas')
      .then(response => {
        this.pizzas = response.data;
        const hubUrl = response.headers.link.match(/<([^>]+)>;\s+rel=(?:mercure|"[^"]*mercure[^"]*")/)[1]; // the autodiscovery mechanism
        // const hubUrl = 'http://localhost:3000/.well-known/mercure'; // what's currently in the demo project
        const es = new EventSource(`${hubUrl}?topic=${document.location.origin}/api/pizzas/{id}`);
        es.onmessage = ({data}) => {
          const responsePizza = JSON.parse(data);
          console.log(responsePizza);
        }
      });
}
```

A few explanations :
* the RegEx in the code above (`/<([^>]+)>;\s+rel=(?:mercure|"[^"]*mercure[^"]*")/`) extracts (discovers) the hub URL from the link header, that is : `http://localhost:3000/.well-known/mercure`.
* the `const eventSource` is **how you can actually subscribe to a topic**. You instantiate an EventSource, which *opens a persistent connection to an HTTP server, which sends events in text/event-stream format. (...) Once the connection is opened, incoming messages from the server are delivered to your code in the form of events. ([MDN web docs](https://developer.mozilla.org/en-US/docs/Web/API/EventSource))* You give the EventSource the subscription URI, which is made up of the hub URL and the topic URI.
* as I said before, the topic URI starts with your domain : so to retrieve it, you can look for the origin location of the document (`${document.location.origin}`).
* you subscribe to the topic, but you don't do anything with what is published until you listen to it with the `onmessage` event handler. The parameter of the callback function allows you to retrieve the data that was published to the hub (the pizza that was updated, for example), which you then parse to deserialize it and be able to use it as an object.
* ***And that's it !*** You do whatever you want with the data... you log it, you use it to update the data in your front-end app, you send a notification with info about the data you received... That's up to you. Whatever you do, everyone who is subscribing to the topic will know about it in real-time!

A few more things to know :
* (if you subscribe to a topic which was published via API Platform) when a resource is created or updated, API Platform publishes to the hub the data of this (new or updated) resource. When a resource is deleted, it only publishes the IRI of the deleted resource (for example : `{@id: "/api/pizzas/3"}`).
* in the Pizza App, we only need to subscribe to one topic. In the publishing/subscribing demo however, we need to subscribe to more topics. The subscription URL could look like this : `${hubUrl}?topic=${document.location.origin}/api/pizzas/{id}&topic=${document.location.origin}/api/users/{id}`, but it is easier to read if you write it like this (and this is how it is done in the project) :
  ```javascript
  const url = new URL(hubUrl);
  const domain = 'document.location.origin';
  url.searchParams.append('topic', `${domain}/api/pizzas/{id}`);
  url.searchParams.append('topic', `${domain}/api/users/{id}`);
  
  const es = new EventSource(url.toString(), {withCredentials: true});
  ```
### Send private updates in Mercure (from 0.10 version of Mercure)
#### What is a private update?
A private update is an update that is not _public_, which means it is an update that only specific persons will receive. If an update is private, then only the persons who subscribe to at least one of the topics of the update will receive the update.

#### Publish private updates
You publish private updates almost exactly as public updates, the only difference being that you have to add `true` as the third argument of the `Update` constructor.

```php
$update = new Update(
        [
            'http://localhost/api/pizzas/2',
            'http://localhost/api/food',
        ],
        json_encode($pizza),
        true
    );
    
    $publisher($update);
}
```

Only those who have subscribed to the topic `http://localhost/api/pizzas/2` (or `http://localhost/api/pizzas/{id}`) or to the topic `http://localhost/api/food` (or to both topics) will receive this update.

#### Subscribe to topics of a private update
If the client bears no JWT to the hub of Mercure, it will only receive *public* updates. For them to receive *private* updates, they have to bear a JWT, whose payload is structured like this :
```json
{
  "mercure": {
    "subscribe": [
        "http://localhost/api/pizzas/{id}",
        "http://localhost/api/food"
    ]
  }
}
```

Notice the `subscribe` attribute instead of the `publish` one (in the payload of the publisher's JWT). The array of topics (there can be one, or more) represents to which topic(s) the subscriber subscribes. This way, if a private update is made with a topic they subscribe to, they will receive it.

Don't forget that the verify signature of this JWT must also be signed with the private key you put in your docker-compose file (or elsewhere depending on how you installed the hub).

If the client is a web browser, the JWT will be sent through a `mercureAuthorization` cookie, that will be set by the app through the `set-cookie` header of the response of the page where the client will subscribe to the hub of Mercure. (Otherwise, the JWT is sent in the `Authorization: Bearer <token>` header of the POST request.)

```php
// App/Controller/SubscribingController.php

public function displaySubscribingView()
{
    // payload jwt : mercure.subscribe = ["http://example.com/website/update", "http://example.com/pizza/creation", "http://example.com/user/1/friend-request"]
    $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InN1YnNjcmliZSI6WyJodHRwOi8vZXhhbXBsZS5jb20vd2Vic2l0ZS91cGRhdGUiLCJodHRwOi8vZXhhbXBsZS5jb20vcGl6emEvY3JlYXRpb24iLCJodHRwOi8vZXhhbXBsZS5jb20vdXNlci8xL2ZyaWVuZC1yZXF1ZXN0Il19fQ.et-4ldVL4zYdD6XRjL8W-QYiZMXnKeLs94zkIY6LW68';

    $response = new Response();
    $response->headers->set(
        'set-cookie',
        'mercureAuthorization='.$token.'; Path=/.well-known/mercure; secure; httponly; SameSite=strict'
    );

    return $this->render('subscribing.html.twig', null, $response);
}
```

It is ***important*** to note that the cookie should be `secure` **only** if the connexion is secure (https, which is the best practice). If you test this on your test server which is not secure (http), remove the `secure` attribute of the cookie, otherwise the cookie will **not** be sent to the hub of mercure.

You can (and it is strongly advised) that you set dynamically the JWT of the client, depending on the id of the authenticated user, for example. For that, you can refer to the [Mercure Component documentation](https://symfony.com/doc/current/mercure.html#authorization).

On the client side, ***do not forget*** to set the `withCredentials` attribute of the EventSource to `true`, to tell your browser to send the cookie to the Mercure hub when it instantiates an EventSource.

```javascript
// script.js

subscribe() {
  const hubUrl = 'http://localhost:3000/.well-known/mercure';
  const eventSource = new EventSource(`${hubUrl}?topic=http://localhost/api/pizzas/{id}`, {withCredentials: true});
  eventSource.onmessage = event => {
    const pizza = (JSON).parse(event.data);
    console.log(pizza);
  }
}
```

If you want to see a concrete example, go to the `SubscribingController.php` (in the *src/Controller* folder) and the `subscribing.html.twig` (in the *templates* folder) of this project.

### Use the targets in Mercure (before 0.10 version of Mercure)
Follow these lines _only_ if for some reason you use a version of Mercure older than 0.10. This project uses version 0.10, so if you want to see the code of the project when it was using targets, you'll have to checkout to an older commit.

I have written most of what is below in the same way as above, just changing the terms that needed changing, so you don't have to go up if you're using a version older than 0.10. So if you have written the explanations on how to publish private updates, don't be surprised to read almost exactly the same things.

#### What is a target in Mercure?
A target in Mercure is... well, the target of a publication. A target is a URI which identifies a person or a group of persons. For examples, `http://localhost/users/1` or `http://localhost/group/admin` are targets, the first one would identify someone who is a user with id 1, and the second would identify someone who belongs to the group "admin" (or who has the role "admin").

#### Publish to targets
To publish to a certain target, you just have to add an array of targets URI as a third parameter to the constructor of your Update object or, if you use Guzzle, add a third form parameter with key *"target"*, and the URI of the target as value.

```php
// App/Controller/PizzaController.php
 
public function create(PublisherInterface $publisher)
{
    $pizza = json_encode(new Pizza('Quatre fromages'));

    $update = new Update(
        'http://localhost/notification',
        $pizza,
        [
            'http://localhost/users/1',
            'http://localhost/group/admin'
        ]
    );
    $publisher($update);

    return $this->redirectToRoute('home');
}
```

The code above means that anyone who is identified as `http://localhost/users/1` *or* `http://localhost/group/admin` (and who subscribed to the topic) will receive the publication. The client identified with the target `http://localhost/users/3` will *not* receive the publication, but the client identified with `http://localhost/group/admin` will, even if they're not identified with the target `http://localhost/users/1`.

#### Subscribe as a target
If the client bears not JWT to the hub of Mercure, it will only receive *public* updates. For them to receive *private* updates, they have to bear a JWT, whose payload is structured like this :
```json
{
  "mercure": {
    "subscribe": [
        "http://localhost/users/1"
    ]
  }
}
```

Notice the `subscribe` attribute instead of the `publish` one (in the payload of the publisher's JWT). The array of targets (there can be one, or more) represents as *which* target the subscriber identifies : as which target they will subscribe to topics. This way, if a publication is made to a specific target as which they identify, they will receive it.

Don't forget that the verify signature of this JWT must also be signed with the private key you put in your docker-compose file (or elsewhere depending on how you installed the hub).

If the client is a web browser, the JWT will be sent through a `mercureAuthorization` cookie, that will be set by the app through the `set-cookie` header of the response of the page where the client will subscribe to the hub of Mercure. (Otherwise, the JWT is sent in the `Authorization: Bearer <token>` header of the POST request.)

```php
// App/Controller/SubscribingController.php

public function displaySubscribingView()
{
    // payload jwt : mercure.subscribe = ["http://example.com/user/1", "http://example.com/group/users"]
    $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InN1YnNjcmliZSI6WyJodHRwOi8vZXhhbXBsZS5jb20vdXNlci8xIiwiaHR0cDovL2V4YW1wbGUuY29tL2dyb3VwL3VzZXJzIl19fQ.0zbHD9ST7b-eaVjhGfCPNwzW0WXsEImmW0c1sZvWudQ';

    $response = new Response();
    $response->headers->set(
        'set-cookie',
        'mercureAuthorization='.$token.'; Path=/.well-known/mercure; secure; httponly; SameSite=strict'
    );

    return $this->render('subscribing.html.twig', null, $response);
}
```

It is ***important*** to note that the cookie should be `secure` **only** if the connexion is secure (https, which is the best practice). If you test this on your test server which is not secure (http), remove the `secure` attribute of the cookie, otherwise the cookie will **not** be sent to the hub of mercure.

You can (and it is strongly advised) that you set dynamically the JWT of the client, depending on the id of the authenticated user, for example. For that, you can refer to the [Mercure Component documentation](https://symfony.com/doc/current/mercure.html#authorization).

On the client side, ***do not forget*** to set the `withCredentials` attribute of the EventSource to `true`, to tell your browser to send the cookie to the Mercure hub when it instantiates an EventSource.

```javascript
// script.js

subscribe() {
  const hubUrl = 'http://localhost:3000/.well-known/mercure';
  const eventSource = new EventSource(`${hubUrl}?topic=http://localhost/api/pizzas/{id}`, {withCredentials: true});
  eventSource.onmessage = event => {
    const pizza = (JSON).parse(event.data);
    console.log(pizza);
  }
}
```

If you want to see a concrete example, checkout to an older commit and go to the `SubscribingController.php` (in the *src/Controller* folder) and the `subscribing.html.twig` (in the *templates* folder) of this project.
