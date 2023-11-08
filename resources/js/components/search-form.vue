<template>
    <div class="row">
        <div class="col col-md-8">
            <input type="text" class="form-control" id="postal-code" aria-describedby="postal-code-help" placeholder="Postleitzahl" v-model="searchParams['post_code']">
            <div id="postal-code-help" class="form-text ms-2">Bitte geben Sie eine Postleitzahl ein.</div>
        </div>
        <div class="col col-md-4">
            <a href="javascript:void(0)" class="btn btn-primary" @click="requestData()">Suchen</a>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col col-md-4">
            <label class="d-inline" for="type">Spritsorte</label>
            <select class="form-select" id="type" aria-label="Spritsorte" v-model="searchParams['type']">
                <option value="all">Alle</option>
                <option value="e5">E5</option>
                <option value="e10">E10</option>
                <option value="diesel">Diesel</option>
            </select>
        </div>
        <div class="col col-md-4">
            <label class="d-inline" for="sort">Sortierung</label>
            <template v-if="searchParams['type'] == 'all'">
                <select class="form-select" id="sort" aria-label="Sortierung" v-model="searchParams['sort_by']" disabled>
                    <option value="dist" selected>Entfernung</option>
                </select>
            </template>
            <template v-else>
                <select class="form-select" id="sort" aria-label="Sortierung" v-model="searchParams['sort_by']">
                    <option value="dist">Entfernung</option>
                    <option value="price">Preis</option>
                </select>
            </template>
        </div>
        <div class="col col-md-4">
            <label for="sort">Umkreis</label>
            <select class="form-select" id="radius" aria-label="Radius" v-model="searchParams['radius']">
                <option value="2.5">2,5 km</option>
                <option value="5">5 km</option>
                <option value="7.5">7,5 km</option>
                <option value="10">10 km</option>
            </select>
        </div>
    </div>
</template>
<script>
import {MapIcon, MapPinIcon} from "@heroicons/vue/24/solid/index.js";
import {ClockIcon} from "@heroicons/vue/24/outline/index.js";

export default ({
    components: {
        MapIcon,
        MapPinIcon,
        ClockIcon,
    },
    props: ['searchParams'],
    emits: ['requestData'],
    data() {
        return {
          sortBy: null,
        }
    },
    methods: {
        requestData() {
            this.$emit('requestData');
        }
    }
});
</script>