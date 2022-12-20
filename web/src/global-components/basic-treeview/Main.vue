<script setup>
import { computed, ref, toRef } from "vue";

</script>

<template>
    <div class="basictreeview_container">
        <p>Caption Here</p>
        <ul class="basictreeview">
            <li>Animals
                <ul>
                    <li>Birds</li>
                    <li>Mammals
                        <ul>
                            <li>Elephant</li>
                            <li>Mouse</li>
                        </ul>
                    </li>
                    <li>Reptiles</li>
                </ul>
            </li>
            <li>Plants
                <ul>
                    <li>Flowers
                        <ul>
                            <li>Rose</li>
                            <li>Tulip
                                <ul>
                                    <li>List item 1</li>
                                    <li>List item 2
                                        <ul>
                                            <li>List item 2.1</li>
                                            <li>List item 2.2</li>
                                            <li>List item 2.3</li>
                                        </ul>
                                    </li>
                                    <li>List item 3</li>
                                    <li>List item 4</li>
                                    <li>List item 5
                                        <ul>
                                            <li>List item 5.1</li>
                                            <li>List item 5.2
                                                <ul>
                                                    <li>List item 5.2.1</li>
                                                    <li>List item 5.2.2</li>
                                                    <li>List item 5.2.3</li>
                                                    <li>List item 5.2.4</li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </li>
                                    <li>List item 6</li>
                                    <li>List item 7</li>
                                    <li>List item 8</li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li>Trees</li>
                </ul>
            </li>
        </ul>
    </div>
</template>