# Mercure Demo Project
`http://your-domain/` will display a Demo of the use of Mercure in a small Pizza App.  
`http://your-domain/publish` & `http://your-domain/subscribe` will display a Demo of the use of targets in Mercure.
 
# How to use Mercure in a Symfony & Vue.js App

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
* [Set up Mercure](#set-up-mercure)
* [Use Mercure](#use-mercure)

### Initialize a project
Go [there](https://gitlab.eolas.fr/indus/symfony/website-skeleton) and clone on your marchine the skeleton which best suits your needs. (For this project I used the "current-mysql" branch, which at the time initialized a 4.3 Symfony project.)

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

Install your project :
``make project-install``

Install the Symfony plugin which implements Mercure :
``composer require mercure``

Then, you need to generate a JWT token that your Symfony application must bear to be able to *publish* updates to the Mercure Hub. Go to [jwt.io](https://jwt.io/#debugger-io?token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InB1Ymxpc2giOltdfX0.rQB2YPCYz8NX2V1k_a9G3E_AQ6i_1JidlOrOEhUtJaw). The payload should at least contain the following structure :  
```json
{
  "mercure": {
    "publish": []
  }
}
```
This means that the Symfony app will only be authorized to publish public updates (ie. it won't be able to publish to private targets, but will see that later). Replace the empty array by `["*"]` to allow the app to publish to every target (public updates and updates to all private targets).

Don't forget to set your private key in the "verify signature" panel of the jwt.io form. This key should be the same as the one you put in your docker-compose file.

You can now set your environment variables :
```
MERCURE_PUBLISH_URL=http://localhost:8003/.well-known/mercure
# put below the JWT token you've just generated
MERCURE_JWT_TOKEN=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InB1Ymxpc2giOltdfX0.Oo0yg7y4yMa1vr_bziltxuTCqb8JVHKxp-f_FwwOim0
```

Now go to your hub address (`http://localhost:8003/.well-known/mercure`), if you see *Missing "topic" parameter* be displayed : you're all set !

### Use Mercure
You can use Mercure several ways : with or without API Plaftorm, with or without Vue.js, Vuex... and even with or without Symfony. This demo project (regarding the Pizza App) uses Symfony, API Platform, and Vue.js. As for the demo of the use of targets, it's Symfony only (but could easily be done without framework).

#### Installation
Now that you've cloned the skeleton and configured the Mercure hub, let's install API Platform and Vue.js.

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

#### API Platform does the publishing part
Set up your entities and your front-end app. API Platform does almost all the work for you : you won't have to make any publishing ! For this to work, you have to tell API Platform that it should use Mercure. For that, the ApiResource annotation of your Entity should look like this : `@ApiResource(mercure=true)`.

Now, let's take the example of this project. A user can create, edit, or delete pizzas. All of these crud operations make an api call to `/api/pizzas/{id}`, whether it's with the method POST, PUT, or DELETE. If we update the pizza whose id is 2, API Platform will publish to the hub with the following topic : `http://localhost/api/pizzas/2`. (Note that the first part of the topic url is the domain where your project is located.)

So, if you want to know when the pizza 2 is updated, the url you should subscribe to on the client side is : `http://localhost:8003/hub?topic=http://localhost/api/pizzas/2`. *Yes, but I have a thousand pizzas in my database, and I want to add more...* Well, just subscribe to `http://localhost:8003/hub?topic=http://localhost/api/pizzas/{id}` and Mercure will dynamically change the id so that you subscribe in one time to the publications concerning *all* the pizzas.

#### Subscribing to topics
To subscribe to topics on your front-end app, it's as simple and as short as that :
```javascript
// assets/js/view/Pizzas.vue

getPizzas() {
    HTTP_API
      .get('/pizzas')
      .then(response => {
        this.pizzas = response.data['hydra:member'];
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
* `hydra:member` is because the project accepts *ld+json* as response format (and API Platform sends response in this format), the pizzas are thus the value of the `hydra:member` attribute of the response data.
* you *could* brutally copy/paste the subscription url we determined earlier, but then your code wouldn't be very reusable and could be the source of errors, and also you may not know the hub url if you're only in charge of developing the front-end. (*And* the specifications of Mercure suggests discovering the hub url, not hard-coding it.)
* on each api call you make, Mercure sends a *Link* header on the response with the Hub url. The header looks like this : `<http://localhost:8003/.well-known/mercure>; rel="mercure"` and the RegEx in the code above extracts just the hub url, that is : `http://localhost:8003/.well-known/mercure`.
* the `const es` is **how you can actually subscribe to a topic**. You instantiate an EventSource, which *opens a persistent connection to an HTTP server, which sends events in text/event-stream format. (...) Once the connection is opened, incoming messages from the server are delivered to your code in the form of events. ([MDN web docs](https://developer.mozilla.org/en-US/docs/Web/API/EventSource))* You give the EventSource the subscription url, which is made up of the hub url and the topic url.
* as I said before, the topic url starts with your domain : to retrieve it, you look for the origin location of the document.
* you subscribe to the topic, but you don't do anything with what is published until you listen to it with the `onmessage` event handler. The parameter of the callback function allows you to retrieve the data that was published to the hub (the pizza that was updated, for example), which you then parse to deserialize it and be able to use it as an object.
* ***And that's it !*** You do whatever you want with the data... you log it, you use it to update the data in your front-end app, you send a notification with info about the data you received... That's up to you. But whatever you do, everyone who is subscribing to the topic will know about it in real-time !

A few more things to know :
* When a resource is created or updated, API Platform publishes to the hub the data of this (new or updated) resource. When a resource is deleted, it only publishes the IRI of the deleted resource (for example : `{@id: "/api/pizzas/3"}`).
* In this simple project, we only need to subscribe to one topic. If you wanted to subscribe to two (or more) topics at the same time, your subscription url could look like this : `${hubUrl}?topic=${document.location.origin}/api/pizzas/{id}&topic=${document.location.origin}/api/users/{id}`.
