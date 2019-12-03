<template>
    <b-modal :id="modal" :title="title" @show="reset" @ok="savePizza">
        <div class="form-group">
            <label for="createName">Nom</label>
            <input
                    type="text"
                    class="form-control"
                    id="createName"
                    v-model="pizzaSelected.name"
                    placeholder="Entrer le nom de la pizza"
            />
        </div>
        <div class="form-group">
            <label for="createDescription">Description</label>
            <input
                    type="text"
                    class="form-control"
                    id="createDescription"
                    v-model="pizzaSelected.description"
                    placeholder="Entrer la description de la pizza"
            />
        </div>
    </b-modal>
</template>

<script>
  export default {
    name: "Modal",
    props: {
      pizza: Object,
      title: String,
      modal: String
    },
    data: function () {
      return {
        pizzaSelected: {
          id: this.pizza && this.pizza.id ? this.pizza.id : null,
          name: this.pizza && this.pizza.name ? this.pizza.name : null,
          description: this.pizza && this.pizza.description ? this.pizza.description : null,
          owner: this.pizza && this.pizza.owner ? this.pizza.owner : null
        }
      }
    },
    methods: {
      savePizza () {
        this.$emit('pizzaSaved', this.pizzaSelected);
      },
      reset () {
        this.pizzaSelected = {
          id: this.pizza && this.pizza.id ? this.pizza.id : null,
          name: this.pizza && this.pizza.name ? this.pizza.name : null,
          description: this.pizza && this.pizza.description ? this.pizza.description : null
        }
      }
    },
  }
</script>
