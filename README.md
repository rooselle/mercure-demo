# Mercure Demo Project
`http://your-domain/` will display a Demo of the use of Mercure in a small Pizza App.  
`http://your-domain/publish` & `http://your-domain/subscribe` will display a Demo of the use of targets in Mercure.
 
# How to use Mercure in a Web App

> Mercure is a protocol allowing to push data updates to web browsers and other HTTP clients in a convenient, fast, reliable and battery-efficient way. It is especially useful to publish real-time updates of resources served through web APIs, to reactive web and mobile apps.
>
> Mercure is a modern and convenient replacement of Websockets.
>
> — [mercure.rocks](mercure.rocks)

## How does Mercure work ?

### Too long; didn't read
To put it simply, Mercure has a *hub* which is located at some url : in this project, it's your domain on port 8003 at the hub page (let's say `http://localhost:8003/.well-known/mercure`). When something happens on the server and you want every clients to know about it at the same time without them having to refresh the page, you *publish* data to the hub under a *topic*, to inform what this publication is about. On the client side, you *subscribe* to this topic : whenever there's a publication on the particular topic you subscribe to, you'll now be informed and you can act accordingly (display a notification, for example).

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
* [Use the targets in Mercure](#use-the-targets-in-mercure)
  * [What is a target ?](#what-is-a-target-)
  * [Publish to targets](#publish-to-targets)
  * [Subscribe as a target](#subscribe-as-a-target)

### Initialize a project
If you want to make a Symfony App, go [there](https://gitlab.eolas.fr/indus/symfony/website-skeleton) and clone on your marchine the skeleton which best suits your needs. (For this project I used the "current-mysql" branch, which at the time initialized a 4.3 Symfony project.)

### Set up Mercure
Add the following code to your docker-compose :
```json
# .docker/docker-compose.yml

mercure:
  image: dunglas/mercure
  container_name: mercure_container_name
  environment:
      JWT_KEY: aVerySecretKey # put here whatever secret key you wish
      ALLOW_ANONYMOUS: 1
      PUBLISH_ALLOWED_ORIGINS: '*'
      CORS_ALLOWED_ORIGINS: http://localhost # set this to your domain url (without apostrophes !)
  ports:
      - 8003:80
```
It is very important to set properly the *CORS_ALLOWED_ORIGINS* attribute, otherwise the clients won't be able to send their authorization cookie to the Mercure hub.

(Run the two following commands if you've created a Symfony App.)

Install your project :
``make project-install``

Install the Symfony Component which implements Mercure :
``composer require mercure``

Then, you need to generate a JWT token that your application must bear to be able to *publish* updates to the Mercure Hub. Go to [jwt.io](https://jwt.io/#debugger-io?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InB1Ymxpc2giOltdfX0.rQB2YPCYz8NX2V1k_a9G3E_AQ6i_1JidlOrOEhUtJaw). The payload should at least contain the following structure :  
```json
{
  "mercure": {
    "publish": []
  }
}
```
This means that the app will only be authorized to publish public updates (ie. it won't be able to publish to private targets, but will see that later). Replace the empty array by `["*"]` to allow the app to publish to every target (public updates and updates to all private targets).

Don't forget to set your private key in the "verify signature" panel of the jwt.io form. This key should be the same as the one you put in your docker-compose file (or elsewhere depending on how you installed the hub).

You can now set your environment variables (if you're in a Symfony app) :
```
MERCURE_PUBLISH_URL=http://localhost:8003/.well-known/mercure
# put below the JWT token you've just generated
MERCURE_JWT_TOKEN=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InB1Ymxpc2giOltdfX0.Oo0yg7y4yMa1vr_bziltxuTCqb8JVHKxp-f_FwwOim0
```

Now go to your hub address (`http://localhost:8003/.well-known/mercure`), if you see *Missing "topic" parameter* be displayed : you're all set !

### Use Mercure
You can use Mercure several ways : with or without API Plaftorm, with or without Vue.js, Vuex... and even with or without Symfony. This demo project (regarding the Pizza App) uses Symfony, API Platform, and Vue.js. As for the demo of the use of targets, it's Symfony only (but could easily be done without framework).

#### Installation
Now that you've installed your app and configured the Mercure hub, let's install API Platform and Vue.js (pass this step if you don't want to use them).

Install API Platform :
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

So, if you want to know when the pizza 2 is updated, the url you should subscribe to on the client side is : `http://localhost:8003/hub?topic=http://localhost/api/pizzas/2`. *Yes, but I have a thousand pizzas in my database, and I want to add more...* Well, just subscribe to `http://localhost:8003/hub?topic=http://localhost/api/pizzas/{id}` and Mercure will dynamically change the id so that you subscribe in one time to the publications concerning *all* the pizzas.

#### Publish with the Mercure Component of Symfony
To publish an update without API Platform, but with the help of the Mercure Component of Symfony, put the below code (and adapt it, of course) in your controller.
```php
// App/Controller/PizzaController.php

use Symfony\Component\Mercure\PublisherInterface;
// ...

public function update(PublisherInterface $publisher)
    {
        $pizza = $this->pizzaRepository->findById(2);
        $pizza->setName("A new name");
  
        $update = new Update(
            'http://localhost/api/pizzas/2',
            json_encode($pizza)
        );
        
        $publisher($update);

        return $this->redirectToRoute('home');
    }
```

What Symfony is actually doing : it makes an HTTP POST request to the hub of Mercure (which you have previously configured in your .env file), with `http://localhost/api/pizzas/2` as topic, and `json_encode($pizza)` (the modified and encoded pizza) as data.

If you want to see a concrete example, go to the PublishingController (in the *src/Controller* folder) of this project.

#### Publish with Guzzle
As said just before, Symfony just made an HTTP POST request to the hub of Mercure, it means anyone can make an HTTP POST request, even if they're not using the Mercure Component, and even if it's not a framework. To publish an update with Guzzle, copy/paste (and adapt) the below code.

```php
// MyClass.php

use GuzzleHttp\Client;
// ... 

public function create()
{
    $pizza = json_encode(new Pizza('Quatre fromages'));

    $client = new Client(['base_uri' => 'http://localhost:8003']);
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
  const hubUrl = 'http://localhost:8003/.well-known/mercure';
  const eventSource = new EventSource(`${hubUrl}?topic=http://localhost/api/pizzas/{id}`);
  eventSource.onmessage = event => {
    const pizza = (JSON).parse(event.data);
    console.log(pizza);
  }
}
```

Actually, when you'll implement it on your project, you will do things a little differently. Indeed, the [specification](https://mercure.rocks/spec) of Mercure indicates that the hub URL should be discoverable, which implies that on the client side, the hub URL should be discovered rather than hard-coded. Thus you'll have to set a *Link* header with the hub URL (`<http://localhost:8003/.well-known/mercure>; rel="mercure"`) in the response of a GET request when a resource is fetched. If you use API Platform, you don't have to do anything on your app, it sends the header for you.

```javascript
// assets/js/view/Pizzas.vue

getPizzas() {
    axios
      .get('http://localhost/api/pizzas')
      .then(response => {
        this.pizzas = response.data;
        const hubUrl = response.headers.link.match(/<([^>]+)>;\s+rel=(?:mercure|"[^"]*mercure[^"]*")/)[1];
        const es = new EventSource(`${hubUrl}?topic=${document.location.origin}/api/pizzas/{id}`);
        es.onmessage = ({data}) => {
          const responsePizza = JSON.parse(data);
          console.log(responsePizza);
        }
      });
}
```

A few explanations :
* the RegEx in the code above (`/<([^>]+)>;\s+rel=(?:mercure|"[^"]*mercure[^"]*")/`) extracts (discovers) the hub URL from the link header, that is : `http://localhost:8003/.well-known/mercure`.
* the `const eventSource` is **how you can actually subscribe to a topic**. You instantiate an EventSource, which *opens a persistent connection to an HTTP server, which sends events in text/event-stream format. (...) Once the connection is opened, incoming messages from the server are delivered to your code in the form of events. ([MDN web docs](https://developer.mozilla.org/en-US/docs/Web/API/EventSource))* You give the EventSource the subscription URI, which is made up of the hub URL and the topic URI.
* as I said before, the topic URI starts with your domain : so to retrieve it, you can look for the origin location of the document (`${document.location.origin}`).
* you subscribe to the topic, but you don't do anything with what is published until you listen to it with the `onmessage` event handler. The parameter of the callback function allows you to retrieve the data that was published to the hub (the pizza that was updated, for example), which you then parse to deserialize it and be able to use it as an object.
* ***And that's it !*** You do whatever you want with the data... you log it, you use it to update the data in your front-end app, you send a notification with info about the data you received... That's up to you. But whatever you do, everyone who is subscribing to the topic will know about it in real-time !

A few more things to know :
* (if you subscribe to a topic which was published via API Platform) when a resource is created or updated, API Platform publishes to the hub the data of this (new or updated) resource. When a resource is deleted, it only publishes the IRI of the deleted resource (for example : `{@id: "/api/pizzas/3"}`).
* in this simple project, we only need to subscribe to one topic. If you wanted to subscribe to two (or more) topics at the same time, your subscription URL could look like this : `${hubUrl}?topic=${document.location.origin}/api/pizzas/{id}&topic=${document.location.origin}/api/users/{id}`.

### Use the targets in Mercure
#### What is a target in Mercure ?
A target in Mercure is... well, the target of a publication. A target is a URI which identifies a person or a group of persons. For examples, `http://localhost/users/1` or `http://localhost/group/admin` are targets, the first one would identify someone who is a user with id 1, and the second would identify someone who belongs to the group "admin" (or who has the role "admin").

#### Publish to targets
To publish to a certain target, you just have to add an array of targets URI as a third parameter to the constructor of your Update object or, if you use Guzzle, add a third form parameter with key *"target"* and the URI of the target as value.

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

Notice the *subscribe* attribute instead of the *publish* one (in the payload of the publisher's JWT). The array of targets (there can be one, or more) represents as *which* target the subscriber identifies : as which target they will subscribe to topics. This way, if a publication is made to a specific target as which they identify, they will receive it.

Don't forget that the verify signature of this JWT must also be signed with the private key you put in your docker-compose file (or elsewhere depending on how you installed the hub).

If the client is a web browser, the JWT will be sent through a *mercureAuthorization* cookie, that will be set by the app through the *set-cookie* header of the response of the page where the client will subscribe to the hub of Mercure. (Otherwise, the JWT is sent in the *Authorization: Bearer <token>* header of the POST request.)

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
  const hubUrl = 'http://localhost:8003/.well-known/mercure';
  const eventSource = new EventSource(`${hubUrl}?topic=http://localhost/api/pizzas/{id}`, {withCredentials: true});
  eventSource.onmessage = event => {
    const pizza = (JSON).parse(event.data);
    console.log(pizza);
  }
}
```

If you want to see a concrete example, go to the `SubscribingController.php` (in the *src/Controller* folder) and the `subscribing.html.twig` (in the *templates* folder) of this project.
