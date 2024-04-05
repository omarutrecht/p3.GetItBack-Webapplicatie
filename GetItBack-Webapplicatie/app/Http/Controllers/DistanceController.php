<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Setting;

class DistanceController extends Controller
{
    public function index()
    {
        return view('rit-boeken');
    }

    public function calculate(Request $request)
    {
        $start = $request->start;
        $end = $request->end;
        $apiKey = env('GOOGLE_MAPS_API_KEY');
    
        $response = Http::withoutVerifying()->get("https://maps.googleapis.com/maps/api/distancematrix/json", [
            'origins' => $start,
            'destinations' => $end,
            'units' => 'km',
            'language' => 'nl',
            'key' => $apiKey,
        ]);
    
        if ($response->successful()) {
            $data = $response->json();
            if ($data['rows'][0]['elements'][0]['status'] == 'ZERO_RESULTS') {
                return back()->withErrors('Er kon geen route worden gevonden tussen de opgegeven locaties.');
            }
    
            $distanceValue = $data['rows'][0]['elements'][0]['distance']['value'] / 1000;
    
            $duration = $data['rows'][0]['elements'][0]['duration']['text'];
            $pricePerKm = Setting::where('key', 'price_per_km')->value('value');
            $totalPrice = $distanceValue * $pricePerKm;
    
            return back()->with('result', "Afstand: $distanceValue km, Tijdsduur: $duration, Prijs: €$totalPrice");
        } else {
            return back()->withErrors('Kan de afstand niet berekenen. Voer een geldig adres in.');
        }
    }
      
}
