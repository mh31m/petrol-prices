<template>
    <div class="container rounded mt-5 border-2">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mt-1">Aktuelle Spritpreise</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <form @submit.prevent="requestData()">
                    <div class="row">
                        <div class="col col-md-8">
                            <input type="text" class="form-control" id="postal-code" aria-describedby="postal-code-help" placeholder="Postleitzahl" v-model="postalCode">
                            <div id="postal-code-help" class="form-text">Bitte geben Sie eine Postleitzahl ein.</div>
                        </div>
                        <div class="col col-md-4">
                            <a href="javascript:void(0)" class="btn btn-primary" @click="requestData()">Suchen</a>
                        </div>
                    </div>
                </form>
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="col-12 mb-4">
                <h2>Ergebnisse</h2>
                <template v-if="!isLoading && petrolStations.length > 0">
                    <h4 class="mt-5">Standort</h4>
                    {{ location.postal_code }} {{ location.place_name}}, {{ location.state }}
                    <h4 class="mt-5">Tankstellen</h4>
                    <template v-for="petrolStation in petrolStations">
                        <div class="petrol-station-row ps-2">
                            <div class="row mt-2">
                                <div class="col-12">
                                    <div class="fw-bold">{{ petrolStation.brand ? petrolStation.brand : petrolStation.name }}, {{ petrolStation.place }}</div>
                                    <div class="">{{ petrolStation.street }} {{ petrolStation.houseNumber }}</div>
                                </div>
                            </div>
                            <div class="row pb-2">
                                <div class="col-md-6">
                                    <span class="badge badge-e5 me-2">E5: {{ petrolStation.e5 }}</span>
                                    <span class="badge badge-e10 me-2">E10: {{ petrolStation.e10 }}</span>
                                    <span class="badge badge-diesel">Diesel: {{ petrolStation.diesel }}</span>
                                </div>
                                <div class="col-md-6">
                                        <MapPinIcon class="small-icon ms-2" /> {{ petrolStation.dist }}km
                                        <ClockIcon class="small-icon ms-4" /> {{ petrolStation.isOpen ? 'geöffnet' : 'geschlossen' }}
                                        <MapIcon class="small-icon ms-4" /> <a :href="petrolStation.mapsUrl" target="_blank">Maps</a>
                                </div>
                            </div>
                        </div>
                    </template>
                </template>
                <template v-else-if="isLoading">
                    Daten werden geladen
                </template>
                <template v-else-if="errorString">
                    {{ errorString }}
                </template>
            </div>
        </div>
    </div>
</template>

<script>
import { MapIcon, MapPinIcon } from '@heroicons/vue/24/solid'
import { ClockIcon } from '@heroicons/vue/24/outline'
export default ({
    components: {
        MapIcon,
        MapPinIcon,
        ClockIcon,
    },
    props: ['data'],
    data() {
        return {
            postalCode: '',
            isLoading: false,
            errorString: null,
            petrolStations: [],
            location: null,
        }
    },
    methods: {
        requestData() {
            this.isLoading = true;
            this.petrolStations = [];
            this.location = null;
            axios.get(this.data.request_data_url, {
                params: {
                    postal_code: this.postalCode,
                }
            }).then(response => {
                    console.log(response.data);
                    this.location = response.data.location ? response.data.location : [];
                    this.petrolStations = response.data.petrol_stations ? response.data.petrol_stations : [];
                    this.errorString = response.data.error_string ? response.data.error_string : null;
                    this.isLoading = false;
                })
                .catch(error => {
                    this.errorString = 'Fehler beim Abrufen der Daten: ' + error;
                    this.isLoading = false;
                });
        }
    }
})
</script>