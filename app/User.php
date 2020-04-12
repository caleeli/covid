<?php

namespace App;

use App\Traits\HasMenus;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasMenus;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $zoomX = 1; //2e-5; //2e-6
    protected $zoomY = 1; //2e-5; //2e-6

    public function spflu()
    {
        // poblacion mundial aprox 1918: 1.86 mil millones
        // http://www.unescoetxea.org/ext/futuros/es/theme_c/mod13/uncom13t01s02.htm
        $plots = [];
        $img = imagecreatefrompng(resource_path('images/1918_spanish_flu_waves.png'));
        $height = imagesy($img);
        $width = imagesx($img);
        $xx = 2;
        $zoomX = 0.18;
        $zoomY = 0.5;
        $integral = 0;
        $x0 = 0;
        $y0 = 0;
        $peak = 0;
        $c = 0;
        $peak2 = 0;
        $f = 40000000 / 20871;
        for ($x=0;$x<$width;$x+=$xx) {
            for ($y=0;$y<$height;$y++) {
                $color = imagecolorat($img, $x, $y);
                if ($color !== 0) {
                    $plots[] = ['x' => $x * $zoomX, 'y' => ($height-$y) * $zoomY];
                    $yy = ($height-$y) * $f;
                    $peak2 = max($peak2, ($height-$y));
                    $peak = max($peak, $yy);
                    $integral += ($x-$x0) * $yy - (($x-$x0) * abs($yy - $y0))/2;
                    $c++;
                    $x0 = $x;
                    $y0 = $yy;
                    break;
                }
            }
        }
        error_log("integral= $integral");
        error_log("peak = $peak");
        error_log("peak2= $peak2");
        // => 194px (de la imagen png) = 371807 personas
        return $plots;
    }

    public function spfluint()
    {
        // poblacion mundial aprox 1918: 1.86 mil millones
        // http://www.unescoetxea.org/ext/futuros/es/theme_c/mod13/uncom13t01s02.htm
        $plots = [];
        $img = imagecreatefrompng(resource_path('images/1918_spanish_flu_waves.png'));
        $height = imagesy($img);
        $width = imagesx($img);
        $xx = 2;
        $zoomX = 0.18;
        $zoomY = $this->zoomY;
        $integral = 0;
        $x0 = 0;
        $y0 = 0;
        $peak = 0;
        $c = 0;
        $peak2 = 0;
        $f = 40000000 / 20871;
        for ($x=0;$x<$width;$x+=$xx) {
            for ($y=0;$y<$height;$y++) {
                $color = imagecolorat($img, $x, $y);
                if ($color !== 0) {
                    $yy = ($height-$y) * $f;
                    $peak2 = max($peak2, ($height-$y));
                    $peak = max($peak, $yy);
                    $integral += ($x-$x0) * $yy - (($x-$x0) * abs($yy - $y0))/2;
                    $c++;
                    $x0 = $x;
                    $y0 = $yy;
                    $plots[] = ['x' => $x * $zoomX, 'y' => $integral * $zoomY];
                    break;
                }
            }
        }
        error_log("integral= $integral");
        error_log("peak = $peak");
        error_log("peak2= $peak2");
        // => 194px (de la imagen png) = 371807 personas
        return $plots;
    }

    public function covid()
    {
        $plots = [];
        $covid = Cache::remember('covid', now()->addMinutes(1), function () {
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
        $data = $covid['total'];
        $t0 = strtotime($data[0]['date']);
        $zoomY = $this->zoomY;
        //$zoomX = 93.24 / 44 / 7 / 24 / 60 / 60;
        $zoomX = $this->zoomX;
        foreach ($data as $t => $point) {
            //$t = strtotime($point['date']);
            //$x = ($t - $t0);
            $x = $t / 7;
            $y = $point['confirmed'];
            $plots[] = ['x' => $x * $zoomX, 'y' => $y * $zoomY];
        }
        error_log(json_encode($data[count($data)-1]));
        return $plots;
    }

    public function ah1n1()
    {
        $txt = file_get_contents(app_path('a_h1n1.txt'));
        $data = explode("\n", $txt);
        $zoomY = $this->zoomY;
        $zoomX = $this->zoomX;
        foreach ($data as $t => $line) {
            $line = preg_split('/\s+/', $line);
            $x = $t;
            if (!isset($line[1])) {
                break;
            }
            $y = intval($line[1]);
            $plots[] = ['x' => $x * $zoomX, 'y' => $y * $zoomY];
        }
        return $plots;
    }
}
