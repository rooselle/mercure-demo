<template>
    <div>
        <b-navbar toggleable="lg" type="dark" variant="dark">
            <b-navbar-brand href="https://mercure.rocks/">Mercure</b-navbar-brand>
        </b-navbar>
        <div class="container">
            <pizzas />
            <notifications group="pizza" position="bottom left"></notifications>
        </div>
    </div>
</template>

<script>
  import {HTTP, HTTP_API} from "../http-common";
  import Pizzas from "./Pizzas";

  export default {
    name: 'app',
    components: {
      "pizzas": Pizzas,
    }
  }
</script>
