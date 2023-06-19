<script setup lang="ts" >
import { ref, computed , PropType} from "vue";

type ProductList = {
    brand : Brand,
    product_units : ProductUnits[]

}

type Brand = {
    code : string,
    id : string,
    name : string,
    ulid : string
}

type ProductUnits = {
    code : string,
    id : string,
    is_base : boolean,
    is_primary_unit : boolean,
    ulid : string,
    unit : Unit
}

type Unit = {
    category : string,
    code : string ,
    description:string,
    id : string,
    name : string,
    ulid : string
}

const props = defineProps({
    productList : { type: Array as PropType<ProductList[]> , default : []}
})


</script>

<template>
    <div class="mt-8"></div>
    <div class="intro-y grid grid-cols-12 gap-5 mt-5">
        <div class="intro-y col-span-12 lg:col-span-12">
            <div class="relative">
                <input
                type="text"
                class="form-control py-3 px-4 w-full lg:w-64 box pr-10"
                placeholder="Search item..."
              />
              <i
                class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0 text-slate-500"
                data-lucide="search"
              ></i>
            </div>
            <div class="grid grid-cols-12 gap-5 mt-5">
                <div
                  class="col-span-12 sm:col-span-4 2xl:col-span-3 box p-5 cursor-pointer zoom-in focus:bg-primary"
                  v-if="productList"
                  v-for="(product, index) in productList"
                >
                  <div class="font-medium text-base">
                    {{ product?.brand?.name }}
                  </div>
                  <select
                    class="form-select py-3 px-4 border-[transparent] w-full text-slate-500"
                    onchange=""
                  >
                    <option
                      v-if="product && product.product_units"
                      v-for="(item, index) in product?.product_units"
                      class="text-slate-500"
                    >
                      {{ item?.unit?.name }}
                    </option>
                  </select>
                </div>
            </div>
        </div>


    </div>
    

</template>