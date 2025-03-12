<?php

namespace App\Console\Commands;

use App\Models\Chapter;
use App\Services\ChapterPaginationService;
use Illuminate\Console\Command;

class PaginateChapters extends Command
{
    protected $signature = 'chapters:paginate {chapter_id?}';
    protected $description = 'Paginate all chapters or a specific chapter';

    public function handle(ChapterPaginationService $paginationService)
    {
        $chapterId = $this->argument('chapter_id');
        
        if ($chapterId) {
            // Paginate a specific chapter
            $chapter = Chapter::findOrFail($chapterId);
            $this->paginateChapter($paginationService, $chapter);
        } else {
            // Paginate all chapters
            $chapters = Chapter::all();
            $this->info("Paginating {$chapters->count()} chapters...");
            
            $bar = $this->output->createProgressBar($chapters->count());
            $bar->start();
            
            foreach ($chapters as $chapter) {
                $this->paginateChapter($paginationService, $chapter, false);
                $bar->advance();
            }
            
            $bar->finish();
            $this->info("\nAll chapters paginated successfully!");
        }
        
        return Command::SUCCESS;
    }
    
    private function paginateChapter(ChapterPaginationService $service, Chapter $chapter, $showOutput = true)
    {
        if ($showOutput) {
            $this->info("Paginating chapter {$chapter->id}: {$chapter->title}");
        }
        
        $pageCount = $service->paginateChapter($chapter);
        
        if ($showOutput) {
            $this->info("Chapter paginated into {$pageCount} pages");
        }
    }
}