<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeoMetaSuggestionsTable extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        Schema::create('seo_meta_suggestions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('metaable_type')->nullable()->index();
            $table->unsignedBigInteger('metaable_id')->nullable()->index();
            $table->string('locale', 12)->nullable()->index();

            $table->string('page_url', 2048)->nullable();
            $table->string('entity_label')->nullable();
            $table->string('entity_title')->nullable();
            $table->text('seo_keywords')->nullable();

            $table->string('title_field')->nullable();
            $table->string('description_field')->nullable();

            $table->text('current_meta_title')->nullable();
            $table->text('current_meta_description')->nullable();
            $table->text('suggested_meta_title')->nullable();
            $table->text('suggested_meta_description')->nullable();
            $table->text('approved_meta_title')->nullable();
            $table->text('approved_meta_description')->nullable();

            $table->string('status', 32)->default('new')->index();
            $table->json('prompt_context')->nullable();
            $table->string('model')->nullable();
            $table->unsignedInteger('tokens_used')->nullable();
            $table->text('error')->nullable();

            $table->timestamp('generated_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedInteger('reviewed_by')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->unsignedInteger('applied_by')->nullable();

            $table->timestamps();

            $table->unique(['metaable_type', 'metaable_id', 'locale'], 'seo_meta_suggestions_identity_unique');
            $table->index(['status', 'locale'], 'seo_meta_suggestions_status_locale_index');
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seo_meta_suggestions');
    }
}
