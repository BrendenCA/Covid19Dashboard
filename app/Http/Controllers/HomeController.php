<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Models\Cases;
use App\Models\Country;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{
    public function dashboard($country_code='ww')
    {
        $date='2020-04-25';

        if($country_code=='ww')
        {
            $country_name='Worldwide';
            list($currentCases, $dailyTotalCases) = self::getWorldWideCases($date);
        }
        else
        {
            $temp = Country::where('code' , '=', $country_code)->first();
            $country_name = $temp->name;
            $country_id = $temp->id;
            list($currentCases, $dailyTotalCases) = self::getCountryCases($country_id, $date);
        }
        
        $dailyTotalCases = $dailyTotalCases->get();
        $reverseDailyTotalCases = $dailyTotalCases->sortByDesc('created_at')->values()->take(6);

        $changeDailyCases = self::getChangeDailyCases($dailyTotalCases);
        $population = self::getPopulationData($country_code);
        $newsData = self::getNewsData($country_name, $country_code);

        return view('dashboard')
                ->with('cases', $currentCases->get())
                ->with('news', $newsData)
                ->with('dtcases', $dailyTotalCases)
                ->with('rdtcases', $reverseDailyTotalCases)
                ->with('dcases', $changeDailyCases)
                ->with('pcount', $population)
                ->with('country_name', $country_name);
    }

    public function getWorldWideCases($date)
    {
        $cases = Cases::where('created_at', '=', $date);
        $dtcases = Cases::select(DB::raw('created_at, sum(count) as count'))->groupBy('created_at');
        return array($cases, $dtcases);
    }

    public function getCountryCases($country_id, $date)
    {
        $cases = Cases::where('created_at', '=', $date)->where('country_id', '=', $country_id);
        $dtcases = Cases::where('country_id', '=', $country_id)->select(DB::raw('created_at, sum(count) as count'))->groupBy('created_at');
        return array($cases, $dtcases);
    }

    public function getPopulationData($country_code)
    {
        $url = "https://restcountries.eu/rest/v2/all?fields=alpha2Code;population";
        $population_data = Http::get($url)->json();
        $pcount = 0;

        foreach($population_data as $p)
        {
            if($country_code=='ww' or $country_code==strtolower($p['alpha2Code']))
            {
                $pcount += $p['population'];
            }
        }
        return $pcount;
    }

    public function getChangeDailyCases($totalCases)
    {
        $dailyCases = collect();
        $oldcount = $totalCases[0]->count;
        $skip = True;
        foreach($totalCases as $dt)
        {
            if($skip)
            {
                $skip=False;
                continue;
            }
            $dailyCases->push(['created_at' => $dt->created_at, 'count'=> ($dt->count - $oldcount)]);
            $oldcount = $dt->count;
        }
        return $dailyCases;
    }

    public function getNewsData($country_name, $country_code)
    {
        $url = "http://newsapi.org/v2/top-headlines?sortBy=publishedAt&pageSize=4&apiKey=dc610658fa1f4e5d9c48089c7c0cc83a&q=covid";
        $newsapiValidCcodes = array('ae','ar','at','au','be','bg','br','ca','ch','cn','co','cu','cz','de','eg','fr','gb','gr','hk','hu','id','ie','il','in','it','jp','kr','lt','lv','ma','mx','my','ng','nl','no','nz','ph','pl','pt','ro','rs','ru','sa','se','sg','si','sk','th','tr','tw','ua','us','ve','za');

        if($country_name != 'Worldwide')
        {
            if(in_array($country_code, $newsapiValidCcodes))
                $url .= '&country=' . $country_code;
            else
                $url .= ' ' . $country_name;
        }
        $news = Http::get($url)->json();
        return $news;
    }
}
