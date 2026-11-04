<?php
namespace App\Services;

class CanvasAiBasket
{
    public static function enabled()
    {
        return (bool) config('canvas_ai.enabled');
    }

    public static function attach(array $item)
    {
        unset($item['canvas_ai'],$item['canvas_ai_fee']);
        $id=request()->input('canvas_ai_id');
        unset($item['canvas_ai_id']);
        if(!$id)return $item;
        abort_unless(self::enabled()&&auth()->check()&&is_string($id)&&preg_match('/^[a-f0-9]{32}$/D',$id),422);
        $path=config('canvas_ai.storage_path').'/'.$id;
        abort_unless(is_file($path.'.json')&&is_file($path.'.png'),422);
        $meta=json_decode(file_get_contents($path.'.json'),true);
        abort_unless((string)($meta['user']??'')===(string)auth()->id()&&($meta['expires']??0)>time()&&isset($meta['cost']['fee_eur']),422);
        $size=$item['sizeId']??$item['size']??'';
        abort_unless(preg_match('/^(\d+(?:\.\d+)?)x(\d+(?:\.\d+)?)/',$size,$m)&&
            abs((float)$m[1]-$meta['width_cm'])<0.01&&abs((float)$m[2]-$meta['height_cm'])<0.01,422);
        $accepted=config('canvas_ai.storage_path').'/accepted/'.auth()->id();
        if (!is_dir($accepted)) mkdir($accepted,0700,true);
        if (!is_file($accepted.'/'.$id.'.png')) { abort_unless(copy($path.'.png',$accepted.'/'.$id.'.png'),500); chmod($accepted.'/'.$id.'.png',0600); }
        $item['canvas_ai']=['file'=>'canvas-ai/accepted/'.auth()->id().'/'.$id.'.png','id'=>$id,'fee_eur'=>$meta['cost']['fee_eur'],'border_cm'=>3.5,'rect'=>$meta['rect'],
            'width_cm'=>$meta['width_cm'],'height_cm'=>$meta['height_cm'],
            'source_image'=>$item['savedImage']??null,'preview_image'=>$item['activeImage']??null];
        return $item;
    }

    public static function totals(array &$basket,&$total,&$noDiscount)
    {
        $seen=[];
        foreach($basket as &$item){
            if(!is_array($item)||empty($item['canvas_ai']))continue;
            $ai=$item['canvas_ai'];$id=$ai['id'];
            // Cart edits invalidate approval just as editor crop changes do.
            if(isset($ai['width_cm']) && (!preg_match('/^(\d+(?:\.\d+)?)x(\d+(?:\.\d+)?)/',$item['sizeId']??'',$m) ||
                abs((float)$m[1]-$ai['width_cm'])>=0.01 || abs((float)$m[2]-$ai['height_cm'])>=0.01 ||
                ($item['savedImage']??null)!==($ai['source_image']??null) || ($item['activeImage']??null)!==($ai['preview_image']??null))) {
                unset($item['canvas_ai'],$item['canvas_ai_fee']);
                continue;
            }
            $fee=isset($seen[$id])?0:(int)$ai['fee_eur'];$seen[$id]=true;
            $item['canvas_ai_fee']=$fee;
            $item['sumPrice']=(float)($item['sumPrice']??0)+$fee;
            $item['sumFormatedPrice']=number_format($item['sumPrice'],2,',',' ').' €';
            $total+=$fee;$noDiscount+=$fee;
        }
        unset($item);
    }
}
