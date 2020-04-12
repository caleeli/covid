<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;

class UpdateCovid implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Cache::remember('covid', now()->addMinutes(1), function () {
            $covid = json_decode(file_get_contents('https://pomber.github.io/covid19/timeseries.json'), true);
            $total = [];
            foreach ($covid['US'] as $i=> $us) {
                $date = $us['date'];
                $deaths = 0;
                $confirmed = 0;
                $recovered = 0;
                foreach ($covid as $k => $d) {
                    $deaths += $d[$i]['deaths'];
                    $confirmed += $d[$i]['confirmed'];
                    $recovered += $d[$i]['recovered'];
                }
                $total[] = compact('date', 'deaths', 'confirmed', 'recovered');
            }
            $covid['total'] = $total;
            return $covid;
        });
    }
}
