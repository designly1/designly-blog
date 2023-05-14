<?php
class Page
{
    private $title;
    private $content;
    private $data;
    private $template;

    static public function replaceKeys($string, $array)
    {
        return preg_replace_callback('/{{(.*?)}}/', function ($matches) use ($array) {
            return isset($array[$matches[1]]) ? $array[$matches[1]] : $matches[0];
        }, $string);
    }

    static public function cleanUpTags($string)
    {
        return preg_replace('/{{.*?}}/', '', $string);
    }

    static public function removeEmptyLines($str)
    {
        // Split the string into an array of lines
        $lines = explode("\n", $str);

        // Loop through each line and remove if it's empty or contains only whitespace
        foreach ($lines as $key => $line) {
            if (trim($line) === '') {
                unset($lines[$key]);
            }
        }

        // Re-join the remaining lines and return the result
        return implode("\n", $lines);
    }

    static public function includeTemplateFiles($str)
    {
        global $thisUrl;
        return preg_replace_callback('/{{inc:(.*?)}}/', function ($matches) {
            $file = COMP_DIR . trim($matches[1]) . '.html';
            if (file_exists($file)) {
                ob_start();
                require $file;
                $content = ob_get_clean();

                return $content;
            } else {
                throw new RuntimeException('Template file not found: ' . $file);
            }
        }, $str);
    }

    static public function minifyHtml($html)
    {
        $search = array(
            '/\>[^\S ]+/s',  // strip whitespaces after tags, except space
            '/[^\S ]+\</s',  // strip whitespaces before tags, except space
            '/(\s)+/s'       // shorten multiple whitespace sequences
        );
        $replace = array(
            '>',
            '<',
            '\\1'
        );
        return preg_replace($search, $replace, $html);
    }


    static public function prettifyHtml($html)
    {
        $html = self::minifyHtml($html);
        $doc = new DOMDocument();
        $doc->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // Set some options to make the output prettier
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;

        // Convert the DOMDocument back to a string of HTML
        return $doc->saveHTML();
    }

    static public function hashDir($dir)
    {
        $timestamps = array();
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $timestamps[] = $file->getMTime();
            }
        }
        sort($timestamps);
        $hash = hash('sha256', implode('', $timestamps));
        return $hash;
    }

    static public function parseTemplate($file)
    {
        global $thisUrl;
        ob_start();
        require($file);
        $content = ob_get_clean();

        return $content;
    }

    static public function insertCopyButtons($html)
    {
        $dom = new DOMDocument();
        $dom->formatOutput = false;
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

        // Format code blocks
        $pres = $dom->getElementsByTagName('pre');
        foreach ($pres as $pre) {
            $div = $dom->createElement('div');
            $div->setAttribute('class', 'code-wrapper shadow-lg');
            $pre->parentNode->insertBefore($div, $pre);
            $div->appendChild($pre);

            $span1 = $dom->createElement('span');
            $span1->setAttribute('class', 'mac1');
            $div->insertBefore($span1);
            $span2 = $dom->createElement('span');
            $span2->setAttribute('class', 'mac2');
            $div->insertBefore($span2);
            $span3 = $dom->createElement('span');
            $span3->setAttribute('class', 'mac3');
            $div->insertBefore($span3);

            $button = $dom->createElement('button');
            $button->setAttribute('class', 'code-button');
            $button->setAttribute('aria-label', 'Copy to clipboard');
            $i = $dom->createElement('i');
            $i->setAttribute('class', 'fa-solid fa-copy code-copy-icon');
            $button->appendChild($i);
            $div->appendChild($button);
        }

        // Format images
        $imgs = $dom->getElementsByTagName('img');
        foreach ($imgs as $img) {
            $img->setAttribute('class', 'blog-post-image');
            $img->setAttribute('width', '1920');
            $img->setAttribute('height', '1080');
            $src = $img->getAttribute('src');
            $src = str_replace('d340jo5zum8tsx.cloudfront.net', 'cdn.designly.biz',  $src);
            $img->setAttribute('data-original', $src);
            $srcset = Helper::mkSrcSet($src);
            $src = str_replace('cdn.designly.biz', 'cdn.designly.biz/imgr',  $src);
            $src .= '?w=1200&q=75';
            $img->setAttribute('src', $src);
            $img->setAttribute('srcset', $srcset);
        }

        // Format links
        $links = $dom->getElementsByTagName('a');
        foreach ($links as $link) {
            $link->setAttribute('rel', 'noreferrer noopener');
            $link->setAttribute('target', '_blank');
        }

        $modifiedHtml = $dom->saveHTML();
        $modifiedHtml = preg_replace('/^.*?<body.*?>|<\/body>.*$/si', '', $modifiedHtml);
        $modifiedHtml = preg_replace('/^.*?<html.*?>|<\/html>.*$/si', '', $modifiedHtml);

        return $modifiedHtml;
    }

    public function __construct($template, $data)
    {
        if (!defined('MAIN_TEMPLATE_FILE')) {
            throw new RuntimeException('MAIN_TEMPLATE_FILE not defined');
        }
        if (!file_exists(MAIN_TEMPLATE_FILE)) {
            throw new RuntimeException('MAIN_TEMPLATE_FILE not found');
        }
        $this->template = $template;
        $this->data = $data;

        return $this->build();
    }

    public function build()
    {
        global $thisUrl;
        if (!file_exists($this->template)) {
            throw new RuntimeException('Template not found');
        }
        // Get main template
        ob_start();
        require MAIN_TEMPLATE_FILE;
        $this->content = ob_get_clean();

        // Get page template
        ob_start();
        require $this->template;
        $pageContent = ob_get_clean();
        // Insert page content
        $this->content = str_replace('{{PAGE::CONTENT}}', $pageContent, $this->content);
        // Include template files
        $this->content = self::includeTemplateFiles($this->content);
        // Insert code copy buttons
        if (isset($this->data['content'])) {
            $this->data['content'] = self::insertCopyButtons($this->data['content']);
        }
        // Insert data keys
        $this->content = self::replaceKeys($this->content, $this->data);
        // Clean up tags
        $this->content = self::cleanUpTags($this->content);
        // Remove empty lines
        $this->content = self::removeEmptyLines($this->content);

        return true;
    }

    public function render()
    {
        header('Content-Type: text/html; charset=utf-8');
        echo $this->content;
    }

    public function write($filename)
    {
        return file_put_contents($filename, $this->content);
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public function getTemplate()
    {
        return $this->template;
    }
}
