<div class="tab-content">
    <h4>Tree View</h4>
    <div id="tree-container"></div>
</div>



<style type="text/css">
    .tree-container>svg {
        width: 100% !important;
    }

    .node {
        cursor: pointer;
    }

    .overlay {
        background-color: #EEE;
    }

    .node circle {
        fill: #fff;
        stroke: steelblue;
        stroke-width: 1.5px;
    }

    .node text {
        font-size: 10px;
        font-family: sans-serif;
    }

    .link {
        fill: none;
        stroke: #ccc;
        stroke-width: 1.5px;
    }

    .templink {
        fill: none;
        stroke: red;
        stroke-width: 3px;
    }

    .ghostCircle.show {
        display: block;
    }

    .ghostCircle,
    .activeDrag .ghostCircle {
        display: none;
    }
</style>