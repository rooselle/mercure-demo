<template>
    <div class="item">
        <b-card :title="pizza.name">
            <b-card-text>
                {{ pizza.description }}
            </b-card-text>
            <b-card-text>
                Mis à jour <timeago :datetime="pizza.updatedAt"></timeago>
            </b-card-text>
            <b-button v-b-modal="modalName" variant="primary">Edit</b-button>
            <b-button variant="danger" @click="deletePizza(pizza)">Delete</b-button>
            <b-form-checkbox @change="changePizzaSelection(pizza)"></b-form-checkbox>
        </b-card>
        <modal
                :pizza="pizza"
                :title="pizza.name"
                :modal="modalName"
                @pizzaSaved="updatePizza"
        />
    </div>
</template>

<script>
  import Modal from "./Modal";

  export default {
    name: "Pizza",
    props: {
      pizza: Object,
    },
    components: {
      'modal': Modal
    },
    data: function () {
      return {
        modalName: 'modal-' + this.pizza.id,
      }
    },
    methods: {
      updatePizza(itemEdited) {
        this.$emit('updatePizza', itemEdited);
      },
      deletePizza(pizzaToDelete) {
        this.$emit('deletePizza', pizzaToDelete);
      },
      changePizzaSelection(selectedPizza) {
        this.$emit('change', selectedPizza);
      }
    }
  }
</script>
