<?php
namespace App\Services;

class CanvasAiCost
{
    public static function exchange()
    {
        // Isolate this feature from legacy cache entries created by root CLI jobs.
        // Create new public-rate cache files as the web user; never broaden old file permissions.
        $directory=config('canvas_ai.storage_path').'/fx-cache';
        if(!is_dir($directory) && !mkdir($directory,0700,true) && !is_dir($directory))throw new \RuntimeException('Exchange cache unavailable');
        $cache=new \Illuminate\Cache\Repository(new \Illuminate\Cache\FileStore(new \Illuminate\Filesystem\Filesystem,$directory));
        return $cache->remember('ecb-usd-v1', 3600, function () {
            $xml=\Illuminate\Support\Facades\Http::timeout(10)->get('https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml')->throw();
            $text=$xml->body();
            if(!preg_match('/currency=[\'"]USD[\'"]\s+rate=[\'"]([0-9.]+)[\'"]/',$text,$m) ||
                !preg_match('/time=[\'"]([0-9-]+)[\'"]/',$text,$date))throw new \RuntimeException('Exchange unavailable');
            $rate=(float)$m[1];
            if($rate<0.5||$rate>2||strtotime($date[1])<time()-7*86400)throw new \RuntimeException('Exchange invalid');
            return ['usd_per_eur'=>$rate,'date'=>$date[1],'source'=>'ECB'];
        });
    }

    public static function quote(array $usage,array $fx)
    {
        if(!isset($fx['usd_per_eur']) || !is_numeric($fx['usd_per_eur']) || !is_finite((float)$fx['usd_per_eur']) || $fx['usd_per_eur']<=0)throw new \RuntimeException('Exchange invalid');
        $details=$usage['input_tokens_details']??[];
        foreach(['text_tokens','image_tokens'] as $k)if(!isset($details[$k])||!is_numeric($details[$k])||!is_finite((float)$details[$k])||$details[$k]<0)throw new \RuntimeException('Usage missing');
        $output=$usage['output_tokens_details']??[];
        foreach(['text_tokens','image_tokens'] as $k)if(!isset($output[$k])||!is_numeric($output[$k])||!is_finite((float)$output[$k])||$output[$k]<0)throw new \RuntimeException('Output usage missing');
        if(!isset($usage['output_tokens'])||$usage['output_tokens']<=0||
            $output['text_tokens']+$output['image_tokens']!=$usage['output_tokens']||!empty($details['cached_tokens']))throw new \RuntimeException('Unsupported usage');
        // GPT Image 1.5 published standard USD rates per million tokens.
        $usd=($details['text_tokens']*5+$details['image_tokens']*8+$output['text_tokens']*10+$output['image_tokens']*32)/1000000;
        $eur=$usd/$fx['usd_per_eur'];
        if(!is_finite($eur)||$eur<0||$eur>PHP_INT_MAX)throw new \RuntimeException('Cost invalid');
        return ['usage'=>$usage,'model'=>'gpt-image-1.5','usd'=>$usd,'eur'=>$eur,'fee_eur'=>round(ceil($eur * 10 - 1e-9) / 10, 2),'fx'=>$fx,'tariff_checked'=>'2026-11-04'];
    }
}
