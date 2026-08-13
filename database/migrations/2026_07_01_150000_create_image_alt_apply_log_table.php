<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImageAltApplyLogTable extends Migration
{
    private const TABLE = 'image_alt_apply_log';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (Schema::hasTable(self::TABLE)) {
            return;
        }

        Schema::create(self::TABLE, function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('suggestion_id');
            $table->string('imageable_type')->nullable();
            $table->unsignedBigInteger('imageable_id')->nullable();
            $table->string('field')->nullable();
            $table->string('locale', 20)->nullable();
            $table->string('source_type', 50)->nullable();
            $table->unsignedBigInteger('media_id')->nullable();
            $table->string('column_written');
            $table->longText('old_value')->nullable();
            $table->longText('new_value')->nullable();
            $table->unsignedBigInteger('applied_by')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->string('source', 20)->default('cli');

            $table->index('suggestion_id', 'image_alt_apply_log_suggestion_index');
            $table->index(['imageable_type', 'imageable_id'], 'image_alt_apply_log_imageable_index');
            $table->index('locale', 'image_alt_apply_log_locale_index');
            $table->index('media_id', 'image_alt_apply_log_media_index');
            $table->index(['field', 'column_written'], 'image_alt_apply_log_column_index');
        });
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
}
