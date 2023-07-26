<script setup lang="ts">
import { ref, computed, PropType } from "vue";

type ProductList = {
  brand: Brand;
  product_units: ProductUnits[];
};

type Brand = {
  code: string;
  id: string;
  name: string;
  ulid: string;
};

type ProductUnits = {
  code: string;
  id: string;
  is_base: boolean;
  is_primary_unit: boolean;
  ulid: string;
  unit: Unit;
};

type Unit = {
  category: string;
  code: string;
  description: string;
  id: string;
  name: string;
  ulid: string;
};

const props = defineProps({
  productList: { type: Array as PropType<ProductList[]>, default: [] },
});
const search = ref<string>('');


const emits = defineEmits<{
  (e : 'handleClickProductUnit', data:ProductList) : void,
  (e : 'handleSearchProduct', value:string) :void
}>()
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
          v-model="search"
          @change="$emit('handleSearchProduct', search)"
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
          :key="index"
        >
          <div class="font-medium text-base">
            {{ product?.brand?.name }}
          </div>
          <ul>
            <li
              class="ml-5 text-slate-500 text-sm"
              v-if="product && product.product_units"
              v-for="(item, index) in product?.product_units"
              :key="index"
            >
              <p 
                class="cursor-pointer hover:underline"
                @click="$emit('handleClickProductUnit', item)"
              >
                {{ item?.unit?.name }}
              </p>
            </li>
          </ul>
        </div>
      </div>

      <div class="grid grid-cols-12 gap-5 mt-5 pt-5 border-t">
        <a
          href="javascript:;"
          data-tw-toggle="modal"
          data-tw-target="#add-item-modal"
          class="intro-y block col-span-12 sm:col-span-4 2xl:col-span-3"
          v-for="(product, index) in productList"
          :key="index"
        >
          <div class="box rounded-md p-3 relative zoom-in">
            <div
              class="flex-none relative block before:block before:w-full before:pt-[100%]"
            >
              <div class="absolute top-0 left-0 w-full h-full image-fit">
                <img
                  alt="Midone - HTML Admin Template"
                  class="rounded-md"
                  src="dist/images/food-beverage-4.jpg"
                />
              </div>
            </div>
            <div class="block font-medium text-center truncate mt-3">
              {{ product?.brand?.name }}
              <ul class="mt-5 min-h-[50px]  ">
                <li
                  class="text-slate-500 text-sm"
                  v-if="product && product.product_units"
                  v-for="(item, index) in product?.product_units"
                >
                  <p
                    class="cursor-pointer hover:underline"
                    @click="$emit('handleClickProductUnit', item)"
                  >
                    {{ item?.unit?.name }}
                  </p>
                </li>
              </ul>
            </div>
          </div>
        </a>
      </div>

      <div class="box mt-10 pt-2 border-t">
        <a
          href="javascript:;"
          data-tw-toggle="modal"
          data-tw-target="#add-item-modal"
          class="flex items-center p-3 cursor-pointer transition duration-300 ease-in-out bg-white dark:bg-darkmode-600 hover:bg-slate-100 dark:hover:bg-darkmode-400 rounded-md"
          v-for="(product, index) in productList"
          :key="index"
        >
          <div class="max-w-[50%] truncate mr-1">
            {{ product?.brand?.name }}
            <ol class="">
                <li
                  class="ml-3 text-slate-500 text-sm"
                  v-if="product && product.product_units"
                  v-for="(item, index) in product?.product_units"
                >
                  <p 
                    class="cursor-pointer hover:underline"
                    @click="$emit('handleClickProductUnit', item)"
                  >
                    {{ item?.unit?.name }}
                  </p>
                </li>
              </ol>
          </div>
        </a>
      </div>
    </div>
  </div>
</template>

<style>
.zoom-in {
  transform: translate(var(--tw-translate-x), var(--tw-translate-y))
    rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y))
    scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
  cursor: pointer;
  transition-property: color, background-color, border-color, fill, stroke,
    opacity, box-shadow, transform, filter, -webkit-text-decoration-color,
    -webkit-backdrop-filter;
  transition-property: color, background-color, border-color,
    text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter,
    backdrop-filter;
  transition-property: color, background-color, border-color,
    text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter,
    backdrop-filter, -webkit-text-decoration-color, -webkit-backdrop-filter;
  transition-duration: 300ms;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
.zoom-in:hover {
  --tw-scale-x: 1.05;
  --tw-scale-y: 1.05;
  transform: translate(var(--tw-translate-x), var(--tw-translate-y))
    rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y))
    scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));
  --tw-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1),
    0 8px 10px -6px rgb(0 0 0 / 0.1);
  --tw-shadow-colored: 0 20px 25px -5px var(--tw-shadow-color),
    0 8px 10px -6px var(--tw-shadow-color);
  box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000),
    var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
}
</style>
