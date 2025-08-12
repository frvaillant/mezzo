<script setup>

/**
 * Représente un produit et les actions liées
 */

import {onMounted, ref, watch} from "vue"
import Notifier from "../services/Notifier"

const props = defineProps({
    product: {
        type: Object,
        required: true
    }
})

/** Total du prix correspondant au produit */
const total = ref(0)

const quantity = ref(props.product.quantity ?? 0)

const hasConsigne = ref(false)
const consigneButton = ref(null)

const emit = defineEmits(['update-total'])

onMounted(() => {
    document.addEventListener('Purchase', onPurchase)
})

watch(total, (newTotal) => {
    emit('update-total', {
        id: props.product.id,
        quantity: quantity,
        hasConsigne: hasConsigne,
        total: newTotal,
        totalProduct: quantity.value * props.product.unitPrice.toFixed(2)
    })
})

const onPurchase = (e) => {
    const alertStock = e?.detail?.stock[props.product.id]
    const message = `Votre stock de ${props.product.name} est faible`

    if(alertStock) {
        Notifier.warning(message)
    }
}

/**
 *
 * @type {handleClickAdd}
 */
const handleClickAdd = (element => {
    element.classList.add('clicked')
    void element.offsetWidth
    element.style.transition = '0.25s ease'
    const delay = 250
    setTimeout(() => {
        element.classList.remove('clicked')
    }, 100)

    setTimeout(() => {
        element.removeAttribute('style')
    }, delay * 2)
})

/**
 * @param e
 */
const increase = (e) => {
    handleClickAdd(e.currentTarget)
    quantity.value += 1
    calculate()
}

/**
 *
 */
const decrease = () => {
    if (quantity.value === 0) {
        return
    }
    quantity.value -= 1
    calculate()
}

/**
 * Remettre à 0.
 * Cette fonction est exposée et utilisée par le composant Purchase
 */
const reset = () => {
    quantity.value = 0
    consigneButton?.value?.classList.remove('active')
    consigneButton?.value?.classList.remove('bg-' + consigneButton?.value?.dataset.color)
    calculate()
}

/**
 * Expose la fonction reset
 */
defineExpose({reset})

const calculate = () => {
    total.value = quantity.value * props.product.unitPrice.toFixed(2)
    const consigneTotalValue = (quantity.value * props.product.unitPrice.toFixed(2)) + quantity.value
    if (hasConsigne.value) {
        total.value = consigneTotalValue
    }
}

/**
 * @param e
 */
const toggleConsigne = (e) => {
    e.currentTarget.classList.toggle('active')
    e.currentTarget.classList.toggle('bg-' + e.currentTarget.dataset.color)
    hasConsigne.value = !hasConsigne.value
    calculate()
}

</script>

<template>

    <div
        class="product"
        :data-quantity="quantity"
        :class="[
            'bg-' + props.product.color,
            props.product.withConsigne ? 'pb-3' : 'pb-6'
          ]"
    >

        <div class="head flex items-center gap-3 px-2 bg-black/30 text-white rounded-t-md">
            <span class="product-icon" v-html="props.product.picto"></span>
            <span class="product-name text-lg font-bold flex items-center">{{ props.product.name }}</span>
        </div>

        <div class="actions flex justify-between w-full px-2 mt-3">

            <button
                class="increase bg-black/90 p-2 rounded-md"
                @click="increase"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#ffffff" viewBox="0 0 256 256">
                    <path
                        d="M224,128a8,8,0,0,1-8,8H136v80a8,8,0,0,1-16,0V136H40a8,8,0,0,1,0-16h80V40a8,8,0,0,1,16,0v80h80A8,8,0,0,1,224,128Z"></path>
                </svg>
            </button>

            <div class="bg-white text-lg text-gray-500 font-bold flex justify-center items-center w-[45px] rounded-md">
                {{ quantity }}
            </div>

            <button
                class="decrease bg-black/90 p-2 rounded-md"
                @click="decrease"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#ffffff" viewBox="0 0 256 256">
                    <path d="M224,128a8,8,0,0,1-8,8H40a8,8,0,0,1,0-16H216A8,8,0,0,1,224,128Z"></path>
                </svg>
            </button>

        </div>

        <div v-if="props.product.withConsigne" class="flex items-center justify-between w-full mt-2 px-2">

            <div class="flex items-center">

                <button
                    ref="consigneButton"
                    @click="toggleConsigne"
                    class="button consigne flex items-center justify-start gap-3 text-white"
                    :class="[hasConsigne ? 'bg-black' : '']"
                    data-color="black"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#ffffff" viewBox="0 0 256 256">
                        <path
                            d="M206,26.69A8,8,0,0,0,200,24H56a8,8,0,0,0-7.94,9l23.15,193A16,16,0,0,0,87.1,240h81.8a16,16,0,0,0,15.89-14.09L207.94,33A8,8,0,0,0,206,26.69ZM191,40,188.1,64H67.9L65,40ZM168.9,224H87.1L69.82,80H186.18Z"></path>
                    </svg>
                    Consigne
                </button>

                <span v-if="quantity > 0 && !hasConsigne">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#ffffff" viewBox="0 0 256 256"><path
                        d="M232,56A104.11,104.11,0,0,1,128,160H51.31l34.35,34.34a8,8,0,0,1-11.32,11.32l-48-48a8,8,0,0,1,0-11.32l48-48a8,8,0,0,1,11.32,11.32L51.31,144H128a88.1,88.1,0,0,0,88-88,8,8,0,0,1,16,0Z"></path></svg>
                </span>

            </div>

        </div>

    </div>

</template>

<style scoped>

</style>
