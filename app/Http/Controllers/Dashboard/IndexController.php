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

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index(): View
    {
        $this->readSessionValues();
        $this->data['request_data_url'] = route('request_data');
        return view('dashboard', [
            'data' => $this->data,
        ]);
    }

    public function requestData(): JsonResponse
    {
        $this->readRequestParameters();
        $this->getCoordinatesOfpostCode();
        if (isset($this->coordinates)) {
            $this->data['location']['state']       = $this->coordinates['state'];
            $this->data['location']['postal_code'] = $this->coordinates['postal_code'];
            $this->data['location']['place_name']  = $this->coordinates['place_name'];
            $this->getPetrolPrices();
            if (isset($this->petrolStations)) {
                $this->createMapsLinks();
                $this->data['petrol_stations'] = $this->petrolStations;
            }
        }
        $this->readSessionValues();
        $this->setSessionValues();

        return response()->json($this->data);
    }

    private function readRequestParameters(): void
    {
        $this->postCode = $this->request->input('postal_code');
        $this->type = $this->request->input('type');
        $this->sortBy = $this->request->input('sort_by');
        $this->radius = $this->request->input('radius');
    }

    private function getCoordinatesOfpostCode(): void
    {
        $response = Http::get('https://zip-api.eu/api/v1/info/DE-' . $this->postCode);
        if ($response->successful()) {
            $this->coordinates = $response;
        } else {
            $this->data['error_string'] = 'Fehler beim Laden des Standorts.';
        }
    }

    private function getPetrolPrices(): void
    {
        if ($this->type != 'all') {
            $petrolApiUrl = 'https://creativecommons.tankerkoenig.de/json/list.php?lat=' . $this->coordinates['lat'] . '&lng=' . $this->coordinates['lng'] . '&rad=' . $this->radius . '&sort=' . $this->sortBy . '&type=' . $this->type . '&apikey=' . env('TANKERKOENIG_API_KEY');
        } else {
            $petrolApiUrl = 'https://creativecommons.tankerkoenig.de/json/list.php?lat=' . $this->coordinates['lat'] . '&lng=' . $this->coordinates['lng'] . '&rad=' . $this->radius . '&sort=dist&type=all&apikey=' . env('TANKERKOENIG_API_KEY');
        }
        $response = Http::get($petrolApiUrl);
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

    private function readSessionValues(): void
    {
        $this->data['post_codes'] = [];
        if ($this->request->session()->has('post_codes')) {
            $this->data['post_codes'] = $this->request->session()->get('post_codes');
        }
    }

    private function setSessionValues(): void
    {
        if (!isset($this->data['error_string'])) {
            $this->data['post_codes'][] = $this->data['location']['postal_code'];
            $this->data['post_codes'] = array_unique($this->data['post_codes']);
            if (count($this->data['post_codes']) >= 6) {
                array_shift($this->data['post_codes']);
            }
            $this->request->session()->put('post_codes', $this->data['post_codes']);
        }
    }
}
