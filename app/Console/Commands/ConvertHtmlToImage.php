<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ConvertHtmlToImage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:html-to-image {html : public/input.html} {output : public/image.png}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert HTML to image using wkhtmltoimage';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $html = $this->argument('html');
        $output = $this->argument('output');

        exec("wkhtmltoimage --format png $html $output");
        $this->info("HTML converted to image and saved to $output");
    }
}
