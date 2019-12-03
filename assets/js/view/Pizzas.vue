<template>
    <div>
        <h2 class="text-center mt-5">All Pizzas</h2>
        <div class="row">
            <pizza-list
                    class="col-11"
                    :pizzas="pizzas"
                    @deletePizza="deletePizzaFromBase"
                    @deletePizzas="deletePizzasFromBase"
                    @updatePizza="updatePizzaFromBase"
            />
            <div class="col-1">
                <create-button @createPizza="createPizzaInBase"/>
            </div>
        </div>
    </div>
</template>

<script>
  import {HTTP_API} from "../http-common";
  import CreateButton from "../components/CreateButton";
  import PizzaList from "../components/PizzaList";

  export default {
    name: "Pizzas",
    components: {
      "pizza-list": PizzaList,
      "create-button": CreateButton
    },
    data() {
      return {
        pizzas: []
      }
    },
    methods: {
      getPizzas() {
        HTTP_API
          .get('/pizzas')
          .then(response => {
            this.pizzas = response.data['hydra:member'];
            this.subscribeToPizzaUpdate(response);
          })
          .catch(error => {
            this.$notify({
              group: 'pizza',
              title: `Un problème est survenu lors de la récupération des pizzas. Merci de recharger la page ou de contacter un administrateur si le problème se poursuit.`,
              text: error,
              data: {
                error
              },
              type: 'danger'
            });
          });
      },
      createPizza(pizza, pizzas) {
        pizzas.push(pizza);
        this.$notify({
          group: 'pizza',
          title: 'La pizza a bien été créée',
          text: 'Nom : ' + pizza.name + ', Description : ' + pizza.description,
          data: {
            pizza
          },
          type: 'success'
        });
      },
      createPizzaInBase(pizzaToCreate) {
        HTTP_API.post('/pizzas', pizzaToCreate)
          .catch(error => {
            this.$notify({
              group: 'pizza',
              title: 'La pizza ne s\'est pas créée...',
              text: error,
              data: {
                error
              },
              type: 'error'
            });
          });
      },
      deletePizza(pizzaId, pizzas) {
        const pizzaIndex = pizzas.findIndex(p => p['@id'] === pizzaId);
        if (pizzaIndex >= 0) {
          pizzas.splice(pizzaIndex, 1);
          this.$notify({
            group: 'pizza',
            text: `La pizza #${pizzaId.match(/[0-9]+/)[0]} a bien été supprimée.`,
            data: {
              pizzaId
            },
            type: 'success'
          });
        } else {
          this.$notify({
            group: 'pizza',
            text: `La pizza #${pizzaId.match(/[0-9]+/)[0]} a été supprimée par un autre utilisateur mais un problème est survenu lors de la mise à jour de votre affichage. Merci de recharger la page.`,
            data: {
              pizzaId
            },
            type: 'danger'
          });
        }
      },
      deletePizzaFromBase(pizzaToDelete) {
        HTTP_API.delete('/pizzas/' + pizzaToDelete.id)
          .catch(error => {
            this.$notify({
              group: 'pizza',
              title: 'La pizza ' + pizzaToDelete.name + ' n\'a pas été supprimée correctement',
              text: error,
              type: 'error'
            });
          });
      },
      deletePizzasFromBase(data) {
        const pizzasToDelete = data.pizzasToDelete;
        const pizzas = data.pizzas;
        pizzasToDelete.forEach(pizza => {
          this.deletePizzaFromBase(pizza, pizzas);
        });
      },
      updatePizza(pizza, pizzas) {
        const pizzaInList = pizzas.find(p => p.id === pizza.id);
        if (pizzaInList) {
          pizzaInList.name = pizza.name;
          pizzaInList.description = pizza.description;
          pizzaInList.updatedAt = pizza.updatedAt;
          this.$notify({
            group: 'pizza',
            title: 'La pizza a bien été mise à jour',
            text: 'Nom : ' + pizza.name + ', Description : ' + pizza.description,
            data: {
              pizza
            },
            type: 'success'
          });
        } else {
          this.$notify({
            group: 'pizza',
            text: 'La pizza ' + pizza.name + ' a été modifiée par un autre utilisateur mais un problème est survenu lors de la mise à jour de votre affichage. Merci de recharger la page.',
            data: {
              pizza
            },
            type: 'danger'
          });
        }
      },
      updatePizzaFromBase(pizzaToUpdate) {
        HTTP_API.put('/pizzas/' + pizzaToUpdate.id, pizzaToUpdate)
          .catch(error => {
            this.$notify({
              group: 'pizza',
              title: 'La pizza ne s\'est pas bien mise à jour...',
              text: error,
              type: 'error'
            });
          });
      },
      subscribeToPizzaUpdate(response) {
        const hubUrl = response.headers.link.match(/<([^>]+)>;\s+rel=(?:mercure|"[^"]*mercure[^"]*")/)[1];
        const es = new EventSource(`${hubUrl}?topic=${document.location.origin}/api/pizzas/{id}`);
        es.onmessage = ({data}) => {
          const responsePizza = JSON.parse(data);

          if (!responsePizza.id) {
            this.deletePizza(responsePizza['@id'], this.pizzas);
            const pizzaInUserList = this.user.pizzas.findIndex(p => p['@id'] === responsePizza['@id']);
            if (pizzaInUserList >= 0) {
              this.user.pizzas.splice(pizzaInUserList, 1);
            }
            return;
          }

          const pizzaInAppList = this.pizzas.find(p => p.id === responsePizza.id);

          if (pizzaInAppList) {
            this.updatePizza(responsePizza, this.pizzas);
          } else {
            this.createPizza(responsePizza, this.pizzas);
          }
        }
      }
    },
    created() {
      this.getPizzas();
    }
  }
</script>

<style scoped>

</style>