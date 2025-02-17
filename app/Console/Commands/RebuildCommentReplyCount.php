<?php

namespace App\Console\Commands;

use App\Models\Comment;
use Illuminate\Console\Command;

class RebuildCommentReplyCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:rebuild-comment-reply-count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Rebuilding comment reply count...');

        Comment::where('parent_id', 0)->chunk(500, function ($comments) {
            foreach ($comments as $comment) {
                $comment->timestamps = false;
                $comment->reply_count = Comment::where('parent_id', $comment->id)->count();
                $comment->save();
            }

            $this->info('Rebuilding comment reply count chunk last ID ' . $comment->id);
        });

        $this->info('Rebuilding comment reply count completed.');
    }
}
