<?php
require_once __DIR__ . '/../../../../autoload.php';

use Zend\File\ClassFileLocator;

class ClassmapTask extends Task
{
    protected $dir;
    protected $output;
    protected $failonerror = false;

    /**
     * directory to search for class files (recursive)
     *
     * @param string $dir
     * @return void
     */
    public function setDir($dir)
    {
        if (!is_dir($dir)) {
            throw new BuildException(sprintf(
                'Directory does not exist: %s',
                realpath($dir)
            ));
        }
        if (!is_writable($dir)) {
            throw new BuildException(sprintf(
                'Directory is not writable: %s',
                realpath($dir)
            ));
        }
        $this->dir = realpath($dir);
    }

    /**
     * output file
     *
     * @param string $output
     * @return void
     */
    public function setOutput($output)
    {
        touch($output);
        $this->output = realpath($output);
    }

    /**
     * if error occured, whether build should fail
     *
     * @param bool $value
     * @return void
     */
    public function setFailonerror($value)
    {
        $this->failonerror = $value;
    }

    /**
     * init
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * main method
     *
     * @return void
     */
    public function main()
    {
        if (!$this->dir) {
            $this->dir = getcwd();
        }

        $this->log(sprintf('Generating classmap file for %s', $this->dir));

        // We need to add the $this->dir into the relative path that is created in the classmap file.
        $classmapPath = str_replace(DIRECTORY_SEPARATOR, '/', realpath(dirname($this->output)));

        // Simple case: $libraryPathCompare is in $classmapPathCompare
        if (strpos($this->dir, $classmapPath) === 0) {
            $relativePathForClassmap = substr($this->dir, strlen($classmapPath) + 1) . '/';
        } else {
            $libraryPathParts  = explode('/', $this->dir);
            $classmapPathParts = explode('/', $classmapPath);

            // Find the common part
            $count = count($classmapPathParts);
            for ($i = 0; $i < $count; $i++) {
                if (!isset($libraryPathParts[$i]) || $libraryPathParts[$i] != $classmapPathParts[$i]) {
                    // Common part end
                    break;
                }
            }

            // Add parent dirs for the subdirs of classmap
            $relativePathForClassmap = str_repeat('../', $count - $i);

            // Add library subdirs
            $count = count($libraryPathParts);
            for (; $i < $count; $i++) {
                $relativePathForClassmap .= $libraryPathParts[$i] . '/';
            }
        }

        // Get the ClassFileLocator, and pass it the library path
        $locator = new ClassFileLocator($this->dir);

        // Iterate over each element in the path, and create a map of
        // classname => filename, where the filename is relative to the library path
        $map = new stdClass;
        foreach ($locator as $file) {
            $filename  = str_replace($this->dir . '/', '', str_replace(DIRECTORY_SEPARATOR, '/', $file->getPath()) . '/' . $file->getFilename());

            // Add in relative path to library
            $filename  = $relativePathForClassmap . $filename;

            foreach ($file->getClasses() as $class) {
                $map->{$class} = $filename;
            }
        }

        // Create a file with the class/file map.
        // Stupid syntax highlighters make separating < from PHP declaration necessary
        $content = '<' . "?php\n"
                 . "// Generated by ZF2's ./bin/classmap_generator.php\n"
                 . 'return ' . var_export((array) $map, true) . ';';

        // Prefix with __DIR__; modify the generated content
        $content = preg_replace("#(=> ')#", "=> __DIR__ . '/", $content);

        // Fix \' strings from injected DIRECTORY_SEPARATOR usage in iterator_apply op
        $content = str_replace("\\'", "'", $content);

        // Remove unnecessary double-backslashes
        $content = str_replace('\\\\', '\\', $content);

        // Exchange "array (" width "array("
        $content = str_replace('array (', 'array(', $content);

        // Align "=>" operators to match coding standard
        preg_match_all('(\n\s+([^=]+)=>)', $content, $matches, PREG_SET_ORDER);
        $maxWidth = 0;

        foreach ($matches as $match) {
            $maxWidth = max($maxWidth, strlen($match[1]));
        }

        $content = preg_replace('(\n\s+([^=]+)=>)e', "'\n    \\1' . str_repeat(' ', " . $maxWidth . " - strlen('\\1')) . '=>'", $content);

        // Write the contents to disk
        file_put_contents($this->output, $content);

        $this->log(sprintf('Wrote classmap file at %s', $this->output));
    }
}
