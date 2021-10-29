<template>
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            {{ t('views.inbox.title') }}
        </h2>
    </div>
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-3 2xl:col-span-2">
            <div class="intro-y box bg-white p-5 mt-6">
                <button type="button" class="btn text-gray-700 dark:text-gray-300 w-full bg-white dark:bg-theme-1 mt-1 hover:btn-secondary">
                    <Edit3Icon class="w-4 h-4 mr-2" />
                    {{ t('components.buttons.compose')}}
                </button>
            </div>
        </div>
        <div class="col-span-12 lg:col-span-9 2xl:col-span-10">
            <div class="intro-y inbox box mt-5">
                <div class="p-5 flex flex-col-reverse sm:flex-row text-gray-600 border-b border-gray-200 dark:border-dark-1">
                    <div class="flex items-center mt-3 sm:mt-0 border-t sm:border-0 border-gray-200 pt-5 sm:pt-0 mt-5 sm:mt-0 -mx-5 sm:mx-0 px-5 sm:px-0">
                        <input class="form-check-input" type="checkbox" @click="checkAll">
                    </div>
                    <div class="flex items-center sm:ml-auto">
                        <div class="dark:text-gray-300">1 - 50 of 5,238</div>
                    </div>
                </div>
                <div class="overflow-x-auto sm:overflow-x-visible">
                    <div class="intro-y">
                        <div class="inbox__item inbox__item--active inline-block sm:block text-gray-700 dark:text-gray-500 bg-gray-100 dark:bg-dark-1 border-b border-gray-200 dark:border-dark-1">
                            <div class="flex px-5 py-3">
                                <div class="w-50 flex-none flex items-center mr-5">
                                    <input class="form-check-input flex-none" type="checkbox" >
                                    <div class="w-6 h-6 flex-none image-fit relative ml-5">
                                        <img alt="" class="rounded-full" :src="assetPath('def-user.png')">
                                    </div>
                                    <div class="inbox__item--sender truncate ml-3">Brad Pitt</div>
                                </div>
                                <div class="w-64 sm:w-auto truncate"> <span class="inbox__item--highlight">There are many variations of passages of Lorem Ips</span> There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomi </div>
                                <div class="inbox__item--time whitespace-nowrap ml-auto pl-10">01:10 PM</div>
                            </div>
                        </div>
                    </div>
                    <div class="intro-y">
                        <div class="inbox__item inbox__item--active inline-block sm:block text-gray-700 dark:text-gray-500 bg-gray-100 dark:bg-dark-1 border-b border-gray-200 dark:border-dark-1">
                            <div class="flex px-5 py-3">
                                <div class="w-50 flex-none flex items-center mr-5">
                                    <input class="form-check-input flex-none" type="checkbox" >
                                    <div class="w-6 h-6 flex-none image-fit relative ml-5">
                                        <img alt="" class="rounded-full" :src="assetPath('def-user.png')">
                                    </div>
                                    <div class="inbox__item--sender truncate ml-3">Russell Crowe</div>
                                </div>
                                <div class="w-64 sm:w-auto truncate"> <span class="inbox__item--highlight">Contrary to popular belief, Lo</span> Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 20 </div>
                                <div class="inbox__item--time whitespace-nowrap ml-auto pl-10">01:10 PM</div>
                            </div>
                        </div>
                    </div>
                    <div class="intro-y">
                        <div class="inbox__item inline-block sm:block text-gray-700 dark:text-gray-500 bg-gray-100 dark:bg-dark-1 border-b border-gray-200 dark:border-dark-1">
                            <div class="flex px-5 py-3">
                                <div class="w-50 flex-none flex items-center mr-5">
                                    <input class="form-check-input flex-none" type="checkbox" checked>
                                    <div class="w-6 h-6 flex-none image-fit relative ml-5">
                                        <img alt="" class="rounded-full" :src="assetPath('def-user.png')">
                                    </div>
                                    <div class="inbox__item--sender truncate ml-3">Morgan Freeman</div>
                                </div>
                                <div class="w-64 sm:w-auto truncate"> <span class="inbox__item--highlight">Contrary to popular belief, Lo</span> Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 20 </div>
                                <div class="inbox__item--time whitespace-nowrap ml-auto pl-10">05:09 AM</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-5 flex flex-col sm:flex-row items-center text-center sm:text-left text-gray-600">
                    <div class="dark:text-gray-300">4.41 GB (25%) of 17 GB used Manage</div>
                    <div class="sm:ml-auto mt-2 sm:mt-0 dark:text-gray-300">Last account activity: 36 minutes ago</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { inject, onMounted } from "vue";
import mainMixins from '../../mixins';

const { t, assetPath } = mainMixins();

onMounted(() => {
    const setDashboardLayout = inject('setDashboardLayout');
    setDashboardLayout(false);
});

function checkAll() {

}

</script>
