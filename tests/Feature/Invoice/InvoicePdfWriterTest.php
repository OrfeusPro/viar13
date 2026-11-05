<?php

namespace Tests\Feature\Invoice;

use App\Http\Controllers\DynamicPDFController;
use App\Http\Controllers\GiftcardController;
use App\Services\Invoice\InvoicePdfWriter;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class InvoicePdfWriterTest extends TestCase
{
    public function test_gift_card_controller_keeps_original_pdf_contract_and_participates_in_rollback(): void
    {
        $disk = Storage::fake('gift-controller-test');
        app()->usePublicPath($disk->path('public'));
        $engine = Mockery::mock();
        $engine->shouldReceive('set_option')->once()->with('isRemoteEnabled', true);
        $engine->shouldReceive('set_option')->once()->with('dpi', 96);
        $pdf = Mockery::mock();
        $pdf->shouldReceive('getDomPDF')->twice()->andReturn($engine);
        $pdf->shouldReceive('loadHTML')->once()->withArgs(fn ($html): bool => str_contains($html, 'FIXTURE-CODE'))->andReturnSelf();
        $pdf->shouldReceive('setPaper')->once()->with([0, 0, 675, 525])->andReturnSelf();
        $pdf->shouldReceive('setWarnings')->once()->with(false)->andReturnSelf();
        $pdf->shouldReceive('output')->once()->andReturn('%PDF fixture gift');
        app()->instance('dompdf.wrapper', $pdf);
        DB::beginTransaction();
        $url = app(GiftcardController::class)->generateGiftCardPDF(7, 123, 9, 50, 'lv', 'FIXTURE-CODE');
        $this->assertSame(url('/storage/pdf-gift-card/123_7_9.pdf'), $url);
        $this->assertSame('%PDF fixture gift', $disk->get('public/storage/pdf-gift-card/123_7_9.pdf'));
        DB::rollBack();
        $this->assertSame([], $disk->allFiles());
    }

    public function test_rollback_restores_original_after_repeated_writes_and_removes_new_document(): void
    {
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $disk = Storage::fake('invoice-rollback-test');
        $disk->put('old.pdf', 'original');
        DB::beginTransaction();
        app(InvoicePdfWriter::class)->write($disk->path('old.pdf'), 'first');
        app(InvoicePdfWriter::class)->write($disk->path('old.pdf'), 'second');
        app(InvoicePdfWriter::class)->write($disk->path('pdf/new.pdf'), 'new');
        app(InvoicePdfWriter::class)->write($disk->path('pdf/new.pdf'), 'new again');
        DB::rollBack();
        $this->assertSame('original', $disk->get('old.pdf'));
        $this->assertSame(['old.pdf'], $disk->allFiles());
    }

    public function test_nested_rollback_restores_parent_then_parent_can_write_and_commit(): void
    {
        $disk = Storage::fake('invoice-rollback-test');
        $disk->put('old.pdf', 'original');
        DB::beginTransaction();
        app(InvoicePdfWriter::class)->write($disk->path('old.pdf'), 'parent');
        DB::beginTransaction();
        app(InvoicePdfWriter::class)->write($disk->path('old.pdf'), 'child');
        DB::rollBack();
        $this->assertSame('parent', $disk->get('old.pdf'));
        app(InvoicePdfWriter::class)->write($disk->path('old.pdf'), 'committed');
        DB::commit();
        $this->assertSame('committed', $disk->get('old.pdf'));
        DB::beginTransaction();
        app(InvoicePdfWriter::class)->write($disk->path('old.pdf'), 'later cancelled');
        DB::rollBack();
        $this->assertSame('committed', $disk->get('old.pdf'));
    }

    public function test_outer_rollback_restores_original_after_two_committed_sibling_transactions(): void
    {
        $disk = Storage::fake('invoice-rollback-test');
        $disk->put('old.pdf', 'original');
        DB::beginTransaction();
        app(InvoicePdfWriter::class)->write($disk->path('old.pdf'), 'parent');
        foreach (['child one', 'child two'] as $contents) {
            DB::transaction(fn () => app(InvoicePdfWriter::class)->write($disk->path('old.pdf'), $contents));
        }
        $this->assertSame('child two', $disk->get('old.pdf'));
        DB::rollBack();
        $this->assertSame('original', $disk->get('old.pdf'));
    }

    public function test_outer_rollback_restores_file_written_only_inside_committed_child(): void
    {
        $disk = Storage::fake('invoice-rollback-test');
        $disk->put('old.pdf', 'original');
        DB::beginTransaction();
        DB::transaction(fn () => app(InvoicePdfWriter::class)->write($disk->path('old.pdf'), 'child'));
        DB::rollBack();
        $this->assertSame('original', $disk->get('old.pdf'));
    }

    public function test_rollback_preserves_detected_external_replacement(): void
    {
        $disk = Storage::fake('invoice-rollback-test');
        $disk->put('old.pdf', 'original');
        DB::beginTransaction();
        app(InvoicePdfWriter::class)->write($disk->path('old.pdf'), 'ours');
        $disk->put('old.pdf', 'external newer document');
        DB::rollBack();
        $this->assertSame('external newer document', $disk->get('old.pdf'));
    }

    public function test_controller_renders_real_pdf_to_isolated_public_path_and_keeps_original_url(): void
    {
        $disk = Storage::fake('invoice-controller-test');
        app()->usePublicPath($disk->path('public'));
        $controller = Mockery::mock(DynamicPDFController::class)->makePartial();
        $controller->shouldReceive('getOrderDataInHtml')->once()->andReturn('<html><body><h1>Invoice fixture</h1><p>75 EUR</p></body></html>');
        $url = $controller->getPDFFromOrder(['id' => 123]);
        $this->assertSame(url('/storage/pdf/123.pdf'), $url);
        $pdf = $disk->get('public/storage/pdf/123.pdf');
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertStringContainsString('%%EOF', $pdf);
        $this->assertSame(['public/storage/pdf/123.pdf'], $disk->allFiles());
    }

    public function test_render_failure_does_not_touch_existing_document(): void
    {
        $disk = Storage::fake('invoice-controller-test');
        app()->usePublicPath($disk->path('public'));
        $disk->put('public/storage/pdf/123.pdf', '%PDF-keep');
        $controller = Mockery::mock(DynamicPDFController::class)->makePartial();
        $controller->shouldReceive('getOrderDataInHtml')->once()->andReturn('<p>Fixture</p>');
        $pdf = Mockery::mock();
        $pdf->shouldReceive('loadHTML')->once()->andReturnSelf();
        $pdf->shouldReceive('setPaper')->once()->with('a4', 'portrait')->andReturnSelf();
        $pdf->shouldReceive('setWarnings')->once()->with(false)->andReturnSelf();
        $pdf->shouldReceive('output')->once()->andThrow(new RuntimeException('Render failed'));
        app()->instance('dompdf.wrapper', $pdf);
        try {
            $controller->getPDFFromOrder(['id' => 123]);
            $this->fail('Rendering failure must propagate.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Render failed', $exception->getMessage());
        }
        $this->assertSame('%PDF-keep', $disk->get('public/storage/pdf/123.pdf'));
        $this->assertSame(['public/storage/pdf/123.pdf'], $disk->allFiles());
    }

    public function test_new_invoice_and_replacement_keep_target_path_and_leave_no_temporary_files(): void
    {
        $disk = Storage::fake('invoice-writer-test');
        $path = $disk->path('pdf/1.pdf');
        $writer = app(InvoicePdfWriter::class);
        $writer->write($path, '%PDF-1.7 old');
        $this->assertSame('%PDF-1.7 old', $disk->get('pdf/1.pdf'));
        $writer->write($path, '%PDF-1.7 new');
        $this->assertSame('%PDF-1.7 new', $disk->get('pdf/1.pdf'));
        $this->assertSame(['pdf/1.pdf'], $disk->allFiles());
    }

    public function test_partial_write_preserves_old_document_and_removes_temporary_file(): void
    {
        $disk = Storage::fake('invoice-writer-test');
        $disk->put('pdf/1.pdf', '%PDF-1.7 keep');
        $files = Mockery::mock(Filesystem::class)->makePartial();
        $files->shouldReceive('put')->once()->andReturnUsing(function ($path): int {
            file_put_contents($path, 'partial');

            return 7;
        });
        $files->shouldNotReceive('move');
        try {
            (new InvoicePdfWriter($files))->write($disk->path('pdf/1.pdf'), '%PDF-1.7 replacement');
            $this->fail('Partial write must fail.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('полностью', $exception->getMessage());
        }
        $this->assertSame('%PDF-1.7 keep', $disk->get('pdf/1.pdf'));
        $this->assertSame(['pdf/1.pdf'], $disk->allFiles());
    }

    public function test_failed_replacement_preserves_old_document_and_removes_temporary_file(): void
    {
        $disk = Storage::fake('invoice-writer-test');
        $disk->put('pdf/1.pdf', '%PDF-1.7 keep');
        $files = Mockery::mock(Filesystem::class)->makePartial();
        $files->shouldReceive('move')->once()->andReturnFalse();
        try {
            (new InvoicePdfWriter($files))->write($disk->path('pdf/1.pdf'), '%PDF-1.7 new');
            $this->fail('Failed replacement must fail.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('заменить', $exception->getMessage());
        }
        $this->assertSame('%PDF-1.7 keep', $disk->get('pdf/1.pdf'));
        $this->assertSame(['pdf/1.pdf'], $disk->allFiles());
    }
}
