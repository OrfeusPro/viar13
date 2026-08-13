<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateImageAltSuggestionsTable extends Migration
{
    private const TABLE = 'image_alt_suggestions';
    private const UNIQUE_INDEX = 'image_alt_suggestions_locale_image_unique';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('imageable_type')->nullable();
            $table->unsignedBigInteger('imageable_id')->nullable();
            $table->string('field')->nullable();
            $table->string('image_path', 1024);
            $table->string('locale', 20)->nullable();
            $table->char('image_hash', 64)->nullable();
            $table->string('page_url', 1024)->nullable();
            $table->text('current_alt')->nullable();
            $table->text('current_title')->nullable();
            $table->text('suggested_alt')->nullable();
            $table->text('suggested_title')->nullable();
            $table->text('approved_alt')->nullable();
            $table->text('approved_title')->nullable();
            $table->enum('status', [
                'new',
                'generated',
                'pending',
                'approved',
                'applied',
                'rejected',
                'failed',
            ])->default('new');
            $table->json('prompt_context')->nullable();
            $table->string('model')->nullable();
            $table->unsignedInteger('tokens_used')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamps();

            $table->index(['imageable_type', 'imageable_id'], 'image_alt_suggestions_imageable_index');
            $table->index('status', 'image_alt_suggestions_status_index');
            $table->index('locale', 'image_alt_suggestions_locale_index');
        });

        $this->addUniqueImageIndex();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(self::TABLE);
    }

    /**
     * MySQL needs prefix lengths here because image_path is intentionally
     * stored as a 1024-character string.
     */
    private function addUniqueImageIndex(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement(
                'ALTER TABLE `' . self::TABLE . '` ADD UNIQUE `' . self::UNIQUE_INDEX . '` ' .
                '(`imageable_type`(191), `imageable_id`, `image_path`(191), `field`(191), `locale`)'
            );

            return;
        }

        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->unique(
                ['imageable_type', 'imageable_id', 'image_path', 'field', 'locale'],
                self::UNIQUE_INDEX
            );
        });
    }
}
