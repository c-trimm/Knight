<?php

namespace Knight;

class Page
{
    private $data = null;
    private $rendered = null;

    public function __construct($file, $data = array())
    {
        ob_start();
        include($file);
        $this->data = array_merge($page, $data);
        $this->data['file'] = $this->getDir() .'/'. $this->getFile();
        $this->data['content'] = ob_get_clean();
    }

    public function get($key, $default = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    public function getDir()
    {
        return $this->get('category', '');
    }

    public function getUrl()
    {
        return $this->getDir() .'/'. $this->get('slug');
    }

    public function getFile()
    {
        return $this->get('slug').'.html';
    }

    public function applyLayout($layout = null, $data = array())
    {
        $layout = App::getDir() . Config::getLayoutsDir() . DIRECTORY_SEPARATOR . ($layout ? $layout : $this->get('layout')) . '.php';
        $data = array_merge($this->data, $data);

        ob_start();
        include($layout);
        $data['content'] = ob_get_clean();

        // Template may have a "parent" set in the file. If so, render it
        if (isset($parent)) $data['content'] = $this->applyLayout($parent, $data);

        $this->data['content'] = $data['content'];
        return $this->data['content'];
    }

    public function save()
    {
        $output_dir = App::getDir() . Config::getOutputDir() . DIRECTORY_SEPARATOR . $this->getDir();
        App::ensureDir($output_dir);

        $output_file = $output_dir .'/'. $this->getFile();
        file_put_contents($output_file, $this->data['content']);
    }
}
