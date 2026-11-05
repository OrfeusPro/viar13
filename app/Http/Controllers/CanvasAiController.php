<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;

class CanvasAiController extends Controller
{
    private $traceId;
    private $traceStart;
    private $stage = 'controller_entered';

    private function trace($stage, array $extra = [])
    {
        $this->stage = $stage;
        \Log::info('canvas-ai trace', array_merge(['request_id'=>$this->traceId,'stage'=>$stage,
            'elapsed_ms'=>(int)round((microtime(true)-$this->traceStart)*1000)], $extra));
    }

    private function failure($code, $status, $reason = null)
    {
        $failedStage=$this->stage;
        $this->trace('failed',['error_code'=>$code,'http_status'=>$status,'failed_stage'=>$failedStage,'reason'=>$reason]);
        return response()->json(['code'=>$code,'request_id'=>$this->traceId,'stage'=>$failedStage],$status)
            ->header('Cache-Control','private, no-store');
    }
    private function guard(Request $request)
    {
        abort_unless(config('canvas_ai.enabled'), 404);
        abort_unless($request->user(), 401);
    }

    public function session(Request $request)
    {
        $this->guard($request);
        return response()->json(['csrf'=>csrf_token()])->header('Cache-Control','private, no-store');
    }

    public function generate(Request $request)
    {
        $this->traceStart=microtime(true);
        $id=$request->header('X-Canvas-Request-ID','');
        $this->traceId=preg_match('/^[a-f0-9]{32}$/D',$id)?$id:bin2hex(random_bytes(16));
        $this->guard($request);
        $this->trace('controller_entered');
        try {return $this->generatePreview($request);}
        catch (\Illuminate\Validation\ValidationException $e) {return $this->failure('AI_IMAGE_ERROR',422,'validation');}
        catch (\Throwable $e) {return $this->failure('AI_INTERNAL_ERROR',500,get_class($e));}
    }

    private function generatePreview(Request $request)
    {
        $this->guard($request);
        $data = $request->validate([
            'image' => 'required|file|mimes:png|max:4096|dimensions:min_width=32,min_height=32,max_width=1024,max_height=1024',
            'depth_x' => 'required|numeric|min:0.005|max:0.4',
            'depth_y' => 'required|numeric|min:0.005|max:0.4',
            'width_cm' => 'required|numeric|min:10|max:300',
            'height_cm' => 'required|numeric|min:10|max:300',
        ]);
        $this->trace('input_validated');
        if (!config('canvas_ai.key')) return $this->failure('AI_PROVIDER_ERROR',503,'not_configured');
        $dir = config('canvas_ai.storage_path');
        if (!is_dir($dir)) mkdir($dir,0700,true);
        // A global test budget is atomic, including parallel requests from different accounts.
        $lock = fopen($dir.'/budget.lock','c+');
        if (!$lock || !flock($lock,LOCK_EX|LOCK_NB)) return $this->failure('AI_RATE_LIMIT',429,'local_busy');
        $source = $expanded = $mask = $generated = $finished = null;
        try {
            if(config('canvas_ai.model')!=='gpt-image-1.5')return $this->failure('AI_PROVIDER_ERROR',503,'pricing_unavailable');
            // Remove only expired feature-owned previews, never other uploads.
            foreach (glob($dir.'/*.json') as $metaFile) {
                if (!preg_match('/^[a-f0-9]{32}\.json$/D',basename($metaFile)) || !is_readable($metaFile)) continue;
                $meta=json_decode(file_get_contents($metaFile),true);
                if (isset($meta['expires']) && $meta['expires']<time()) {
                    @unlink(substr($metaFile,0,-5).'.png'); @unlink($metaFile);
                }
            }
            $budget = $dir.'/budget-'.gmdate('Y-m-d').'.json';
            $counts = is_file($budget) ? json_decode(file_get_contents($budget),true) : [];
            $uid = (string)$request->user()->getAuthIdentifier();
            $fingerprint=hash('sha256', hash_file('sha256',$request->file('image')->getRealPath()).':'.json_encode($request->only(['width_cm','height_cm','depth_x','depth_y'])));
            foreach (glob($dir.'/*.json') as $metaFile) {
                if (!preg_match('/^[a-f0-9]{32}\.json$/D',basename($metaFile))) continue;
                $existing=json_decode(file_get_contents($metaFile),true);
                if (($existing['request_id']??'')!==$this->traceId || (string)($existing['user']??'')!==$uid) continue;
                if (($existing['fingerprint']??'')!==$fingerprint) return $this->failure('AI_REQUEST_CONFLICT',409);
                $previewId=basename($metaFile,'.json');
                if (is_file($dir.'/'.$previewId.'.png') && ($existing['expires']??0)>time()) {
                    return response()->json(['status'=>'duplicate','request_id'=>$this->traceId,'id'=>$previewId,
                        'url'=>'/canvas-ai/preview/'.$previewId,'fee_eur'=>$existing['cost']['fee_eur'],
                        'border_cm'=>($existing['border_cm'] ?? 3.5),'rect'=>$existing['rect']])->header('Cache-Control','private, no-store');
                }
            }
            if (($counts['total'] ?? 0)>=config('canvas_ai.daily_limit', 20) || ($counts['users'][$uid] ?? 0)>=config('canvas_ai.user_daily_limit', 5)) {
                return $this->failure('AI_LOCAL_QUOTA',429,'daily_limit');
            }
            $this->trace('exchange_rate');
            $fx=\App\Services\CanvasAiCost::exchange();
            $source = imagecreatefrompng($request->file('image')->getRealPath());
            if (!$source) return $this->failure('AI_IMAGE_ERROR',422);
            $this->trace('mask_started');
            $iw=imagesx($source); $ih=imagesy($source);
            $g=\App\Services\CanvasAiLayout::geometry($data['width_cm'],$data['height_cm']);
            $w=$g['w'];$h=$g['h'];$x=$g['x'];$y=$g['y'];$b=$g['border'];
            $expanded=imagecreatetruecolor(1024,1024);
            imagealphablending($expanded,false); imagesavealpha($expanded,true);
            imagefill($expanded,0,0,imagecolorallocatealpha($expanded,0,0,0,127));
            imagecopyresampled($expanded,$source,$x,$y,0,0,$w,$h,$iw,$ih);
            $mask=imagecreatetruecolor(1024,1024);
            imagealphablending($mask,false); imagesavealpha($mask,true);
            imagefill($mask,0,0,imagecolorallocatealpha($mask,255,255,255,0));
            imagefilledrectangle($mask,$x-$b,$y-$b,$x+$w+$b-1,$y+$h+$b-1,imagecolorallocatealpha($mask,0,0,0,127));
            imagefilledrectangle($mask,$x,$y,$x+$w-1,$y+$h-1,imagecolorallocatealpha($mask,255,255,255,0));
            ob_start(); imagepng($expanded); $imageBytes=ob_get_clean();
            ob_start(); imagepng($mask); $maskBytes=ob_get_clean();
            $this->trace('mask_generated');
            $counts['total']=($counts['total']??0)+1;
            $counts['users'][$uid]=($counts['users'][$uid]??0)+1;
            file_put_contents($budget,json_encode($counts),LOCK_EX);
            $this->trace('provider_request');
            $providerStart=microtime(true);
            $response=Http::withToken(config('canvas_ai.key'))->connectTimeout(10)->timeout(50)
                ->attach('image', $imageBytes, 'front.png', ['Content-Type'=>'image/png'])
                ->attach('mask', $maskBytes, 'mask.png', ['Content-Type'=>'image/png'])
                ->post('https://api.openai.com/v1/images/edits', [
                    'model'=>config('canvas_ai.model'), 'size'=>'1024x1024', 'quality'=>'medium',
                    'input_fidelity'=>'high', 'prompt'=>\App\Services\CanvasAiLayout::prompt($g),
                ]);
            $this->trace('provider_response',['provider_latency_ms'=>(int)round((microtime(true)-$providerStart)*1000),'http_status'=>$response->status()]);
            if ($response->status()!==200) {
                $error=json_decode($response->body(),true);
                $code=$error['error']['code']??'unknown';
                \Log::warning('canvas-ai provider failure',['status'=>$response->status(),'code'=>preg_match('/^[a-z0-9_]+$/i',$code)?$code:'unknown']);
                if($code==='moderation_blocked')return $this->failure('AI_IMAGE_ERROR',422,'moderation_blocked');
                if(in_array($code,['insufficient_quota','billing_hard_limit_reached','billing_not_active'],true))return $this->failure('AI_PROVIDER_QUOTA',503,$code);
                if($response->status()===429)return $this->failure('AI_RATE_LIMIT',429,'provider_rate_limit');
                return $this->failure('AI_PROVIDER_ERROR',502);
            }
            $payload=json_decode($response->body(),true);
            $cost=\App\Services\CanvasAiCost::quote($payload['usage']??[],$fx);
            $bytes=base64_decode($payload['data'][0]['b64_json']??'',true);
            if (!$bytes || strlen($bytes)>20*1024*1024) return $this->failure('AI_INVALID_RESPONSE',502);
            $info=getimagesizefromstring($bytes);
            if (!$info || $info[0]!==1024 || $info[1]!==1024 || $info['mime']!=='image/png') return $this->failure('AI_INVALID_RESPONSE',502);
            $this->trace('output_validated');
            $generated=imagecreatefromstring($bytes);
            list($finished,$rect)=\App\Services\CanvasAiLayout::finish($generated,$expanded,$g);
            ob_start();imagepng($finished);$bytes=ob_get_clean();
            $id=bin2hex(random_bytes(16));
            $this->trace('file_save_started');
            file_put_contents($dir.'/'.$id.'.png',$bytes,LOCK_EX);
            file_put_contents($dir.'/'.$id.'.json',json_encode(['request_id'=>$this->traceId,'fingerprint'=>$fingerprint,'user'=>$uid,'expires'=>time()+86400,
                'width_cm'=>(float)$data['width_cm'],'height_cm'=>(float)$data['height_cm'],'rect'=>$rect,'border_cm'=>\App\Services\CanvasAiLayout::BORDER_CM,'cost'=>$cost]),LOCK_EX);
            chmod($dir.'/'.$id.'.png',0600); chmod($dir.'/'.$id.'.json',0600);
            $this->trace('file_saved');
            $this->trace('response_returned');
            return response()->json(['request_id'=>$this->traceId,'id'=>$id,'url'=>'/canvas-ai/preview/'.$id,
                'fee_eur'=>$cost['fee_eur'],'border_cm'=>\App\Services\CanvasAiLayout::BORDER_CM,'rect'=>$rect])->header('Cache-Control','private, no-store');
        } catch (\Throwable $e) {
            // Never expose credentials, request contents or provider response bodies.
            \Log::warning('canvas-ai generation failure',['exception'=>get_class($e),'file'=>basename($e->getFile()),'line'=>$e->getLine(),
                'curl_errno'=>$e instanceof \GuzzleHttp\Exception\RequestException ? ($e->getHandlerContext()['errno']??null) : null]);
            $errno=$e instanceof \GuzzleHttp\Exception\RequestException ? ($e->getHandlerContext()['errno']??null) : null;
            $code=($errno===28 || $e instanceof \Illuminate\Http\Client\ConnectionException)?'AI_TIMEOUT':($this->stage==='mask_started'?'AI_MASK_ERROR':($this->stage==='file_save_started'?'AI_SAVE_ERROR':($this->stage==='provider_request'?'AI_PROVIDER_ERROR':($this->stage==='provider_response'?'AI_INVALID_RESPONSE':'AI_INTERNAL_ERROR'))));
            return $this->failure($code,502,get_class($e));
        } finally {
            foreach ([$source,$expanded,$mask,$generated,$finished] as $im) if (is_resource($im) || $im instanceof \GdImage) imagedestroy($im);
            flock($lock,LOCK_UN); fclose($lock);
        }
    }

    public function image(Request $request, $id)
    {
        $this->guard($request);
        abort_unless(preg_match('/^[a-f0-9]{32}$/D',$id),404);
        $base=config('canvas_ai.storage_path').'/'.$id;
        abort_unless(is_file($base.'.json') && is_file($base.'.png'),404);
        $meta=json_decode(file_get_contents($base.'.json'),true);
        abort_unless((string)$meta['user']===(string)$request->user()->getAuthIdentifier() && $meta['expires']>time(),404);
        return response()->file($base.'.png',['Content-Type'=>'image/png','Cache-Control'=>'private, no-store','X-Content-Type-Options'=>'nosniff'])->setPrivate();
    }
}
