<?php

use App\Models\Chapter;
use App\Models\Comment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->unsignedBigInteger('series_id')->nullable()->after('parent_id')->default(0);
        });

        // Only use this for small datasets
        Comment::chunk(200, function ($comments) {
            foreach ($comments as $comment) {

                if ($comment->commentable_type === Chapter::class) {
                    $comment->timestamps = false;
                    $comment->series_id = $comment->commentable->series->id;
                    $comment->save();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn('series_id');
        });
    }
};
