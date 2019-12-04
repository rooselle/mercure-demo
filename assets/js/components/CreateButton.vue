<template>
    <div class="create-button">
        <b-button v-b-modal="modalName">
            <i class="fas fa-plus-circle"></i>
        </b-button>
        <modal
                title="Ajouter une nouvelle pizza"
                :modal="modalName"
                @pizzaSaved="createPizza"
        />
    </div>
</template>

<script>
  import Modal from "./Modal";

  export default {
    name: "CreateButton",
    components: {
      'modal': Modal
    },
    data: function () {
      return {
        modalName: 'create-modal'
      }
    },
    methods: {
      createPizza(pizzaToCreate) {
        this.$emit('createPizza', pizzaToCreate);
      }
    }
  }
</script>
