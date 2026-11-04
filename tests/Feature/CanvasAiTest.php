<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CanvasAiBasket;
use App\Services\CanvasAiLayout;
use App\Services\CheckoutCouponService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CanvasAiTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = storage_path('framework/testing/canvas-ai-'.bin2hex(random_bytes(6)));
        mkdir($this->directory, 0700, true);
        config(['canvas_ai.enabled'=>true, 'canvas_ai.key'=>'test-not-a-real-key',
            'canvas_ai.storage_path'=>$this->directory, 'cache.default'=>'array']);
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        app('files')->deleteDirectory($this->directory);
        parent::tearDown();
    }

    private function user(int $id = 9): User
    {
        $user = new User;
        $user->forceFill(['id'=>$id, 'email'=>'canvas-test@example.test']);
        $this->actingAs($user);
        return $user;
    }

    private function png(int $size = 1024, int $red = 20): string
    {
        $image = imagecreatetruecolor($size, $size);
        imagefill($image,0,0,imagecolorallocate($image,$red,80,120));
        ob_start(); imagepng($image); $bytes = ob_get_clean(); imagedestroy($image);
        return $bytes;
    }

    private function input(): array
    {
        $path=$this->directory.'/input.png';
        file_put_contents($path,$this->png(64));
        return ['image'=>new UploadedFile($path,'front.png','image/png',null,true),
            'width_cm'=>40, 'height_cm'=>50, 'depth_x'=>.07, 'depth_y'=>.07];
    }

    private function fakeProvider(int $status = 200): void
    {
        Http::fake([
            'https://www.ecb.europa.eu/*'=>Http::response('<Cube time="'.gmdate('Y-m-d').'">'."<Cube currency='USD' rate='1.1'/></Cube>"),
            'https://api.openai.com/*'=>Http::response($status===200?[
                'data'=>[['b64_json'=>base64_encode($this->png())]],
                'usage'=>['input_tokens_details'=>['text_tokens'=>100,'image_tokens'=>200],
                    'output_tokens'=>1000,'output_tokens_details'=>['text_tokens'=>0,'image_tokens'=>1000]],
            ]:['error'=>['code'=>'rate_limit_exceeded']],$status),
        ]);
    }

    public function test_preview_requires_authentication_and_explicit_enablement(): void
    {
        $this->getJson('/canvas-ai/session')->assertUnauthorized();
        $this->user();
        $this->getJson('/canvas-ai/session')->assertOk()->assertJsonStructure(['csrf']);
        config(['canvas_ai.enabled'=>false]);
        $this->postJson('/canvas-ai/preview',[])->assertNotFound();
        Http::assertNothingSent();
    }

    public function test_preview_validates_image_and_dimensions_before_provider(): void
    {
        $this->user();
        $this->postJson('/canvas-ai/preview',[])->assertStatus(422)->assertJsonPath('code','AI_IMAGE_ERROR');
        $input=$this->input(); $input['width_cm']=1;
        $this->post('/canvas-ai/preview',$input,['Accept'=>'application/json'])->assertStatus(422);
        Http::assertNothingSent();
    }

    public function test_generation_replay_conflict_and_private_image_ownership(): void
    {
        $this->user(); $this->fakeProvider();
        $headers=['Accept'=>'application/json','X-Canvas-Request-ID'=>str_repeat('a',32)];
        $response=$this->post('/canvas-ai/preview',$this->input(),$headers)->assertOk()->assertJsonPath('fee_eur',1);
        $id=$response->json('id');
        $this->assertFileExists($this->directory.'/'.$id.'.png');
        $this->get('/canvas-ai/preview/'.$id)->assertOk()->assertHeader('Cache-Control','no-store, private');
        config(['canvas_ai.daily_limit'=>1]);
        $this->post('/canvas-ai/preview',$this->input(),$headers)->assertOk()->assertJsonPath('status','duplicate')->assertJsonPath('id',$id);
        Http::assertSentCount(2); // One ECB request and one provider request, no replay charge.
        $different=$this->input();$different['width_cm']=60;
        $this->post('/canvas-ai/preview',$different,$headers)->assertStatus(409)->assertJsonPath('code','AI_REQUEST_CONFLICT');
        $this->user(10);
        $this->getJson('/canvas-ai/preview/'.$id)->assertNotFound();
    }

    public function test_provider_errors_and_local_quota_have_no_saved_preview(): void
    {
        $this->user(); $this->fakeProvider(429);
        $this->post('/canvas-ai/preview',$this->input(),['Accept'=>'application/json'])->assertStatus(429)->assertJsonPath('code','AI_RATE_LIMIT');
        $this->assertSame([],glob($this->directory.'/????????????????????????????????.png'));
        config(['canvas_ai.daily_limit'=>0]);
        $this->post('/canvas-ai/preview',$this->input(),['Accept'=>'application/json'])->assertStatus(429)->assertJsonPath('code','AI_LOCAL_QUOTA');
        Http::assertSentCount(2);
    }

    public function test_parallel_budget_lock_rejects_second_generation_without_provider(): void
    {
        $this->user();
        $lock=fopen($this->directory.'/budget.lock','c+');flock($lock,LOCK_EX|LOCK_NB);
        try {
            $this->post('/canvas-ai/preview',$this->input(),['Accept'=>'application/json'])
                ->assertStatus(429)->assertJsonPath('code','AI_RATE_LIMIT');
            Http::assertNothingSent();
        } finally {flock($lock,LOCK_UN);fclose($lock);}
    }

    public function test_another_user_cannot_attach_preview_to_basket(): void
    {
        $this->user();$id=str_repeat('c',32);
        file_put_contents($this->directory.'/'.$id.'.png',$this->png(64));
        file_put_contents($this->directory.'/'.$id.'.json',json_encode(['user'=>'10','expires'=>time()+86400,
            'width_cm'=>40,'height_cm'=>50,'cost'=>['fee_eur'=>1]]));
        request()->merge(['canvas_ai_id'=>$id]);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        CanvasAiBasket::attach(['sizeId'=>'40x50']);
    }

    public function test_mask_finishing_preserves_front_pixels_exactly(): void
    {
        $original=imagecreatefromstring($this->png(1024,200));
        $generated=imagecreatefromstring($this->png(1024,20));
        $geometry=CanvasAiLayout::geometry(40,50);
        [$result,$rect]=CanvasAiLayout::finish($generated,$original,$geometry);
        $border=$geometry['border'];
        $this->assertSame(imagecolorat($original,$geometry['x'],$geometry['y']),imagecolorat($result,$border,$border));
        $this->assertSame(imagecolorat($generated,0,0),imagecolorat($result,0,0));
        $this->assertGreaterThan(0,$rect['x']);
        imagedestroy($original);imagedestroy($generated);imagedestroy($result);
    }

    public function test_basket_attaches_only_owned_preview_and_fee_is_never_discounted(): void
    {
        $this->user();$id=str_repeat('b',32);
        file_put_contents($this->directory.'/'.$id.'.png',$this->png(64));
        file_put_contents($this->directory.'/'.$id.'.json',json_encode(['user'=>'9','expires'=>time()+86400,
            'width_cm'=>40,'height_cm'=>50,'rect'=>['x'=>.1,'y'=>.1,'w'=>.8,'h'=>.8],'cost'=>['fee_eur'=>2]]));
        request()->merge(['canvas_ai_id'=>$id]);
        $item=CanvasAiBasket::attach(['sizeId'=>'40x50','price'=>20,'sumPrice'=>20,'count'=>1,'savedImage'=>'source','activeImage'=>'preview']);
        $this->assertSame(2,$item['canvas_ai']['fee_eur']);
        $this->assertFileExists($this->directory.'/accepted/9/'.$id.'.png');
        $basket=[$item];$total=20;$excluded=0;
        CanvasAiBasket::totals($basket,$total,$excluded);
        $this->assertSame(22,$total);$this->assertSame(2,$excluded);
        $basket['totalPrice']=$total;
        $coupon=app(CheckoutCouponService::class);
        $this->assertSame('20.00',$coupon->calculate($basket,'universal','10%'));
        $this->assertSame('2.00',$coupon->calculate($basket,'giftcard','500'));
        $this->assertSame('2.00',$coupon->calculate($basket,'universal','500'));
        $basket[0]['sizeId']='60x90';$total=20;$excluded=0;
        CanvasAiBasket::totals($basket,$total,$excluded);
        $this->assertArrayNotHasKey('canvas_ai',$basket[0]);
        $this->assertSame(20,$total);
    }
}
