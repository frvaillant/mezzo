<script setup>
import {ref, watch} from "vue";

    const props = defineProps({
        product: {
            type: Object,
            required: true
        }
    })

    const total = ref( 0);
    const quantity = ref(props.product.quantity ?? 0)
    const hasConsigne = ref(false)
    const consigneButton = ref(null)

    const emit = defineEmits(['update-total'])

    watch(total, (newTotal) => {
        emit('update-total', {
            id: props.product.id,
            quantity: quantity,
            hasConsigne: hasConsigne,
            total: newTotal,
            totalProduct: quantity.value * props.product.unitPrice.toFixed(2)
        })
    })

    const handleClickAdd = (element => {
        element.classList.add('clicked')
        void element.offsetWidth;
        element.style.transition = '0.25s ease'
        const delay = 250
        setTimeout(() => {
            element.classList.remove('clicked')
        }, 100)

        setTimeout(() => {
            element.removeAttribute('style')
        }, delay * 2)
    })

    const increase = (e) => {
        handleClickAdd(e.currentTarget)
        quantity.value += 1
        calculate()
    }

    const decrease = () => {
        if(quantity.value === 0) {
            return
        }
        quantity.value -= 1
        calculate()
    }

    const reset = () => {
        quantity.value = 0
        // hasConsigne.value = false
        consigneButton?.value?.classList.remove('active')
        consigneButton?.value?.classList.remove('bg-' + consigneButton?.value?.dataset.color)
        calculate()
    }

    defineExpose({ reset })

    const calculate = () => {
        total.value = quantity.value * props.product.unitPrice.toFixed(2)
        const consigneTotalValue = (quantity.value * props.product.unitPrice.toFixed(2)) + quantity.value
        if(hasConsigne.value) {
            total.value = consigneTotalValue
        }
    }

    const toggleConsigne = (e) => {
        e.currentTarget.classList.toggle('active')
        e.currentTarget.classList.toggle('bg-' + e.currentTarget.dataset.color)
        hasConsigne.value = !hasConsigne.value
        calculate()
    }

</script>

<template>
    <div class="flex flex-col items-start justify-between pb-2  rounded-lg w-full product">

        <div class="text-lg font-medium flex justify-between items-center w-full gap-2">

            <button
                @click="increase"
                class="product-name flex items-center text-md font-bold flex-1 rounded-lg relative"
                :class="['bg-' + props.product.color]"
            >
                <span v-html="props.product.picto" class="product-picto "></span>
                <span class="ms-3 flex-1 text-start h-full flex justify-between pe-2 whitespace-nowrap">
                    {{ props.product.name }}
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="#ffffff" viewBox="0 0 256 256"><path d="M224,128a8,8,0,0,1-8,8H136v80a8,8,0,0,1-16,0V136H40a8,8,0,0,1,0-16h80V40a8,8,0,0,1,16,0v80h80A8,8,0,0,1,224,128Z"></path></svg>
                </span>

            </button>

            <div class="flex items-center gap-4">

                <div
                    class="button  font-bold flex justify-center items-center h-[50px] w-[50px]"
                    :class="quantity === 0 ? 'text-gray-300 bg-white' : `bg-${props.product.color} text-white text-xl`"
                >
                    {{ quantity }}
                </div>

                <button
                    class="button flex justify-center items-center icon-square circle text-white text-lg"
                    @click="decrease"
                    :class="['bg-' + props.product.color]"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="size-8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                    </svg>
                </button>
            </div>

        </div>

        <div v-if="props.product.withConsigne" class="flex items-center justify-between w-full mt-2">

            <div class="flex items-center">
                <button
                    ref="consigneButton"
                    @click="toggleConsigne"
                    class="button consigne flex items-center justify-start gap-3"
                    :class="[hasConsigne ? 'bg-' + props.product.color : '']"
                    :data-color="props.product.color"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#000000" viewBox="0 0 256 256"><path d="M206,26.69A8,8,0,0,0,200,24H56a8,8,0,0,0-7.94,9l23.15,193A16,16,0,0,0,87.1,240h81.8a16,16,0,0,0,15.89-14.09L207.94,33A8,8,0,0,0,206,26.69ZM191,40,188.1,64H67.9L65,40ZM168.9,224H87.1L69.82,80H186.18Z"></path></svg>
                    Consigne
                </button>

                <span v-if="quantity > 0 && !hasConsigne">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000" viewBox="0 0 256 256"><path d="M232,56A104.11,104.11,0,0,1,128,160H51.31l34.35,34.34a8,8,0,0,1-11.32,11.32l-48-48a8,8,0,0,1,0-11.32l48-48a8,8,0,0,1,11.32,11.32L51.31,144H128a88.1,88.1,0,0,0,88-88,8,8,0,0,1,16,0Z"></path></svg>
                </span>
            </div>

        </div>

    </div>

</template>

<style scoped>

</style>
