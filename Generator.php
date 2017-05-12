<?php

namespace Knight;

class Generator
{
    public static $outputDir;
    public static $entries = [];

    public static function generate()
    {
        App::out('Starting Generator...');

        self::$outputDir = App::getDir() . Config::getOutputDir();

        App::out('Creating Output Directory: ' . self::$outputDir);
        App::del(self::$outputDir);
        $success = mkdir(self::$outputDir);
        if (!$success) throw new \Exception('Failed to create output directory: ' . self::$outputDir);

        self::copyAssets();
        self::generateBlogEntries();
        self::generatePages();

        App::out('Finished Site Generation!');
        App::out(PHP_EOL.'===================================================='.PHP_EOL);
    }

    protected static function copyAssets()
    {
        App::out('Copying Assets...');

        // Input Dirs
        $stylesDir  = App::getDir() . Config::getStylesDir();
        $scriptsDir = App::getDir() . Config::getScriptsDir();
        $imagesDir  = App::getDir() . Config::getImagesDir();
        $staticDir  = App::getDir() . Config::getStaticDir();

        // Output Dirs
        $outputStylesDir  = self::$outputDir . DIRECTORY_SEPARATOR . Config::getStylesDir();
        $outputScriptsDir = self::$outputDir . DIRECTORY_SEPARATOR . Config::getScriptsDir();
        $outputImagesDir  = self::$outputDir . DIRECTORY_SEPARATOR . Config::getImagesDir();

        if (is_dir($stylesDir)) {
            App::out('Copying Styles Directory: ' . $stylesDir . ' --> ' . $outputStylesDir);
            App::xcopy($stylesDir, $outputStylesDir);
        }

        if (is_dir($scriptsDir)) {
            App::out('Copying Scripts Directory: ' . $scriptsDir . ' --> ' . $outputScriptsDir);
            App::xcopy($scriptsDir, $outputScriptsDir);
        }

        if (is_dir($imagesDir)) {
            App::out('Copying Images Directory: ' . $imagesDir . ' --> ' . $outputImagesDir);
            App::xcopy($imagesDir, $outputImagesDir);
        }

        if (is_dir($staticDir)) {
            App::out('Copying Static Files: ' . $staticDir . ' --> ' . $outputDir);
            App::xcopy($staticDir, self::$outputDir);
        }

    }

    protected static function generateBlogEntries()
    {
        if (!Config::getBlogEnabled()) return;

        App::out('Generating Blog Entries...');

        $entires_dir = App::getDir() . Config::getEntriesDir() . DIRECTORY_SEPARATOR;

        $entry_files = array_slice(scandir($entires_dir), 2);
        foreach ($entry_files as $file) {
            App::out(App::bold('Generating Blog Entry: ') . $file);

            $entry = new Page($entires_dir.$file);
            $entry->applyLayout();
            $entry->save();

            self::$entries[] = $entry;
        }

        App::out('Sorting Blog Entries...');
        usort(self::$entries, function ($a, $b) {
            if ($b->get('date') > $a->get('date')) return 1;
            else if ($b->get('date') == $a->get('date')) return 0;
            else return -1;
        });
    }

    protected static function generatePages()
    {
        App::out('Generating Pages...');

        $pages_dir = App::getDir() . Config::getPagesDir() . DIRECTORY_SEPARATOR;

        $page_files = array_slice(scandir($pages_dir), 2);
        foreach ($page_files as $file) {
            App::out(App::bold('Generating Page: ') . $file);
            $page = new Page($pages_dir.$file, ['entries' => self::$entries ]);
            $page->applyLayout();
            $page->save();
        }
    }
}
