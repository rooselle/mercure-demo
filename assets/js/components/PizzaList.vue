<template>
    <div class="lists row">
        <div class="item-list col-10 row">
            <pizza
                    v-for="pizza in pizzas"
                    class="col-lg-6"
                    :pizza="pizza"
                    :key="pizza.id"
                    @change="addOrRemoveSelected"
                    @deletePizza="deletePizza"
                    @updatePizza="updatePizza"
            />
        </div>
        <div class="delete-button col-2">
            <b-button variant="danger" @click="deleteSelected">
                <i class="far fa-trash-alt"></i>
            </b-button>
        </div>
    </div>
</template>

<script>
  import Pizza from "./Pizza";

  export default {
    name: "PizzaList",
    components: {
      "pizza": Pizza,
    },
    props: {
      pizzas: Array
    },
    data: function() {
      return {
        selected: []
      }
    },
    methods: {
      addOrRemoveSelected(pizza) {
        if (!this.selected.find(p => p.id === pizza.id)) {
          this.selected.push(pizza);
        } else {
          const index = this.selected.findIndex(p => p.id === pizza.id);
          this.selected.splice(index, 1);
        }
      },
      deletePizza(pizza) {
        this.$emit('deletePizza', pizza);
      },
      deleteSelected() {
        this.$emit('deletePizzas', {
          pizzasToDelete: this.selected,
          pizzas: this.pizzas
        });
        this.selected = [];
      },
      updatePizza(pizza) {
        this.$emit('updatePizza', pizza);
      }
    }
  }
</script>
