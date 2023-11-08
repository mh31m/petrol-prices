<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class IndexController extends Controller
{
    private array $data = [];

    public function index(): View
    {
        $data = [
            'request_data_url' => route('request_data'),
            'loading' => ''
        ];
        return view('dashboard', compact('data'));
    }

    public function requestData(Request $request): JsonResponse
    {
        $this->readRequestParameters($request);
        $this->getCoordinatesOfPostalCode();
        if (isset($this->coordinates)) {
            $this->data['location']['state'] = $this->coordinates['state'];
            $this->data['location']['postal_code'] = $this->coordinates['postal_code'];
            $this->data['location']['place_name'] = $this->coordinates['place_name'];
            $this->getPetrolPrices();
            if (isset($this->petrolStations)) {
                $this->createMapsLinks();
                $this->data['petrol_stations'] = $this->petrolStations;
            }
        }
        return response()->json($this->data);
    }

    private function readRequestParameters($request): void
    {
        $this->postalCode = $request->input('postal_code');
    }

    private function getCoordinatesOfPostalCode(): void
    {
        $response = Http::get('https://zip-api.eu/api/v1/info/DE-' . $this->postalCode);
        if ($response->successful()) {
            $this->coordinates = $response;
        } else {
            $this->data['error_string'] = 'Fehler beim Laden des Standorts.';
        }
    }

    private function getPetrolPrices(): void
    {
        $response = Http::get('https://creativecommons.tankerkoenig.de/json/list.php?lat=' . $this->coordinates['lat'] . '&lng=' . $this->coordinates['lng'] . '&rad=10&sort=dist&type=all&apikey=' . env('TANKERKOENIG_API_KEY'));
        if ($response->successful() && $response['status'] == 'ok') {
            $this->petrolStations = $response['stations'];
        } else {
            $this->data['error_string'] = 'Fehler beim Laden der Tankstellen.';
        }
    }

    private function createMapsLinks(): void
    {
        foreach ($this->petrolStations as &$petrolStation) {
            if ($petrolStation['street'] && $petrolStation['houseNumber']) {
                $petrolStation['mapsUrl'] = 'https://www.google.com/maps/search/?api=1&query=' . str_replace(" ", "+", strtolower(implode(" ", [$petrolStation['street'], $petrolStation['houseNumber'], $petrolStation['postCode'], $petrolStation['place']])));
            } else {
                $petrolStation['mapsUrl'] = 'https://www.google.com/maps/search/?api=1&query=' . str_replace(" ", "+", strtolower(implode(" ", [$petrolStation['name'], $petrolStation['place']])));
            }
        }
    }
}
