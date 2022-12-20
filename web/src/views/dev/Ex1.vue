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

<style>
.basictreeview_container p {
    color:#369;
    font-weight:bold;
}

.basictreeview,
.basictreeview ul {
  margin:0 0 0 1em; /* indentation */
  padding:0;
  list-style:none;
  color:#369;
  position:relative;
}

.basictreeview ul {margin-left:.5em} /* (indentation/2) */

.basictreeview:before,
.basictreeview ul:before {
  content:"";
  display:block;
  width:0;
  position:absolute;
  top:0;
  bottom:0;
  left:0;
  border-left:1px solid;
}

.basictreeview li {
  margin:0;
  padding:0 1.5em; /* indentation + .5em */
  line-height:2em; /* default list item's `line-height` */
  font-weight:bold;
  position:relative;
}

.basictreeview li:before {
  content:"";
  display:block;
  width:10px; /* same with indentation */
  height:0;
  border-top:1px solid;
  margin-top:-1px; /* border top width */
  position:absolute;
  top:1em; /* (line-height/2) */
  left:0;
}

.basictreeview li:last-child:before {
  background:#f1f5f9; /* same with body background */
  height:auto;
  top:1em; /* (line-height/2) */
  bottom:0;
}
</style>

<script setup>
//#region Imports
//#endregion

//#region Declarations
//#endregion

//#region Data - Pinia
//#endregion

//#region Data - UI
//#endregion

//#region Data - Views
//#endregion

//#region onMounted
//#endregion

//#region Methods
//#endregion

//#region Computed
//#endregion

//#region Watcher
//#endregion
</script>
