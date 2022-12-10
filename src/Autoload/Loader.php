<?php

namespace One23\PhalconPhp\Autoload;

use One23\PhalconPhp\Events\AbstractEventsAware;

/**
 * The Phalcon Autoloader provides an easy way to automatically load classes
 * (namespaced or not) as well as files. It also features extension loading,
 * allowing the user to autoload files with different extensions than .php.
 */
class Loader extends AbstractEventsAware
{
    protected ?string $checkedPath = null;

    protected array $classes = [];

    protected array $debug = [];

    protected array $directories = [];

    protected array $extensions = [];

    /**
     * @var string|callable
     */
    protected string|\Closure $fileCheckingCallback = "is_file";

    protected array $files = [];

    protected ?string $foundPath = null;

    protected bool $isRegistered = false;

    protected array $namespaces = [];

    /**
     * Loader constructor.
     */
    public function __construct(protected bool $isDebug = false)
    {
        $this->extensions[hash("sha256", "php")] = "php";
    }

    /**
     * Adds a class to the internal collection for the mapping
     */
    public function addClass(string $name, string $file): Loader
    {
        $this->classes[$name] = $file;

        return $this;
    }

    /**
     * Adds a directory for the loaded files
     */
    public function addDirectory(string $directory): Loader
    {
        $this->directories[hash("sha256", $directory)] = $directory;

        return $this;
    }

    /**
     * Adds an extension for the loaded files
     */
    public function addExtension(string $extension): Loader
    {
        $this->extensions[hash("sha256", $extension)] = $extension;

        return $this;
    }

    /**
     * Adds a file to be added to the loader
     */
    public function addFile(string $file): Loader
    {
        $this->files[hash("sha256", $file)] = $file;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function addNamespace(
        string $name,
        mixed $directories,
        bool $prepend = false
    ): Loader {
        $nsName       = $name;
        $nsSeparator  = "\\";
        $dirSeparator = DIRECTORY_SEPARATOR;
        $nsName       = trim($nsName, $nsSeparator) . $nsSeparator;
        $directories  = $this->checkDirectories($directories, $dirSeparator);

        // initialize the namespace prefix array if needed
        if (!isset($this->namespaces[$nsName])) {
            $this->namespaces[$nsName] = [];
        }

        $source = ($prepend) ? $directories : $this->namespaces[$nsName];
        $target = ($prepend) ? $this->namespaces[$nsName] : $directories;

        $this->namespaces[$nsName] = array_unique(
            array_merge($source, $target)
        );

        return $this;
    }

    /**
     * Autoloads the registered classes
     */
    public function autoload(string $className): bool
    {
        $this->debug = [];

        $this->addDebug("Loading: " . $className);
        $this->fireManagerEvent("loader:beforeCheckClass", $className);

        if (true === $this->autoloadCheckClasses($className)) {
            return true;
        }

        $this->addDebug("Class: 404: " . $className);

        if (true === $this->autoloadCheckNamespaces($className)) {
            return true;
        }

        $this->addDebug("Namespace: 404: " . $className);

        if (
            true === $this->autoloadCheckDirectories(
                $this->directories,
                $className,
                true
            )
        ) {
            return true;
        }

        $this->addDebug("Directories: 404: " . $className);

        $this->fireManagerEvent("loader:afterCheckClass", $className);

        /**
         * Cannot find the class, return false
         */
        return false;
    }

    /**
     * Get the path the loader is checking for a path
     */
    public function getCheckedPath(): ?string
    {
        return $this->checkedPath;
    }

    /**
     * Returns the class-map currently registered in the autoloader
     *
     * @return string[]
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * Returns debug information collected
     *
     * @return string[]
     */
    public function getDebug(): array
    {
        return $this->debug;
    }

    /**
     * Returns the directories currently registered in the autoloader
     *
     * @return string[]
     */
    public function getDirectories(): array
    {
        return $this->directories;
    }

    /**
     * Returns the file extensions registered in the loader
     *
     * @return string[]
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Returns the files currently registered in the autoloader
     *
     * @return string[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Get the path when a class was found
     *
     * @return string|null
     */
    public function getFoundPath(): ?string
    {
        return $this->foundPath;
    }

    /**
     * Returns the namespaces currently registered in the autoloader
     *
     * @return string[]
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    /**
     * Checks if a file exists and then adds the file by doing virtual require
     */
    public function loadFiles(): void
    {
        $files = $this->files;

        foreach($files as $file) {
            $this->fireManagerEvent("loader:beforeCheckPath", $file);

            if (true === $this->requireFile($file)) {
                $this->foundPath = $file;
                $this->fireManagerEvent("loader:pathFound", $file);
            }
        }
    }

    /**
     * Register the autoload method
     */
    public function register(bool $prepend = false): Loader
    {
        if (!$this->isRegistered) {
            $this->loadFiles();

            spl_autoload_register(
                [$this, "autoload"],
                true,
                $prepend
            );

            $this->isRegistered = true;
        }

        return $this;
    }

    /**
     * Register classes and their locations
     */
    public function setClasses(array $classes, bool $merge = false): Loader
    {
        if (!$merge) {
            $this->classes = [];
        }

        foreach ($classes as $name => $className) {
            $this->addClass($name, $className);
        }

        return $this;
    }

    /**
     * Register directories in which "not found" classes could be found
     */
    public function setDirectories(array $directories, bool $merge = false): Loader
    {
        return $this->addToCollection(
            $directories,
            "directories",
            "addDirectory",
            $merge
        );
    }

    /**
     * Sets an array of file extensions that the loader must try in each attempt
     * to locate the file
     */
    public function setExtensions(array $extensions, bool $merge = false): Loader
    {
        if (!$merge) {
            $this->extensions = [];
            $this->extensions[hash("sha256", "php")] = "php";
        }

        foreach ($extensions as $extension) {
            $this->addExtension($extension);
        }

        return $this;
    }

    /**
     * Sets the file check callback.
     *
     * ```php
     * // Default behavior.
     * $loader->setFileCheckingCallback("is_file");
     *
     * // Faster than `is_file()`, but implies some issues if
     * // the file is removed from the filesystem.
     * $loader->setFileCheckingCallback("stream_resolve_include_path");
     *
     * // Do not check file existence.
     * $loader->setFileCheckingCallback(null);
     * ```
     *
     * @throws Exception
     */
    public function setFileCheckingCallback(string|callable|null $method = null): Loader
    {
        if (is_callable($method)) {
            $this->fileCheckingCallback = $method;
        }
        elseif (is_null($method)) {
            $this->fileCheckingCallback = function ($file) {
                return true;
            };
        }
        else {
            throw new Exception(
                "The 'method' parameter must be either a callable or NULL"
            );
        }

        return $this;
    }

    /**
     * Registers files that are "non-classes" hence need a "require". $this is
     * very useful for including files that only have functions
     */
    public function setFiles(array $files, bool $merge = false): Loader
    {
        return $this->addToCollection(
            $files,
            "files",
            "addFile",
            $merge
        );
    }

    /**
     * Register namespaces and their related directories
     */
    public function setNamespaces(array $namespaces, bool $merge = false): Loader
    {
        $dirSeparator = DIRECTORY_SEPARATOR;

        if (!$merge) {
            $this->namespaces = [];
        }

        foreach ($namespaces as $name => $directories) {
            $directories = $this->checkDirectories($directories, $dirSeparator);
            $this->addNamespace($name, $directories);
        }

        return $this;
    }

    /**
     * Unregister the autoload method
     */
    public function unregister(): Loader
    {
        if (true === $this->isRegistered) {
            spl_autoload_unregister(
                [
                    $this,
                    "autoload"
                ]
            );

            $this->isRegistered = false;
        }

        return $this;
    }

    /**
     * If the file exists, require it and return true; false otherwise
     */
    protected function requireFile(string $file): bool
    {
        /**
         * Check if the file specified even exists
         */
        if (false !== call_user_func($this->fileCheckingCallback, $file)) {
            /**
             * Call 'pathFound' event
             */
            $this->fireManagerEvent("loader:pathFound", $file);
            $this->addDebug("Require: " . $file);

            /**
             * Check if the file specified even exists
             */
            require_once $file;

            return true;
        }

        $this->addDebug("Require: 404: " . $file);

        return false;
    }

    /**
     * Adds a debugging message in the collection
     */
    private function addDebug(string $message): void
    {
        if ($this->isDebug) {
            $this->debug[] = $message;
        }
    }

    /**
     * Traverses a collection and adds elements to it using the relevant
     * class method
     */
    private function addToCollection(
        array $collection,
        string $collectionName,
        string $method,
        bool $merge = false
    ): Loader {
        if (!$merge) {
            $this->{$collectionName} = [];
        }

        foreach ($collection as $element) {
            $this->{$method}($element);
        }

        return $this;
    }

    /**
     * Checks the registered classes to find the class. Includes the file if
     * found and returns true; false otherwise
     */
    private function autoloadCheckClasses(string $className): bool
    {
        if (isset($this->classes[$className])) {
            $filePath = $this->classes[$className];

            $this->fireManagerEvent("loader:pathFound", $filePath);

            $this->requireFile($filePath);
            $this->addDebug("Class: load: " . $filePath);

            return true;
        }

        return false;
    }

    /**
     * Checks the registered directories to find the class. Includes the file if
     * found and returns true; false otherwise
     */
    private function autoloadCheckDirectories(
        array $directories,
        string $className,
        bool $isDirectory = false
    ): bool {
        $dirSeparator = DIRECTORY_SEPARATOR;
        $nsSeparator  = "\\";
        $className    = str_replace($nsSeparator, $dirSeparator, $className);
        $extensions   = $this->extensions;

        foreach ($directories as $directory) {
            /**
             * Add a trailing directory separator if the user forgot to do that
             */
            $fixedDirectory = rtrim($directory, $dirSeparator) . $dirSeparator;

            foreach ($extensions as $extension) {
                /**
                 * Create a possible path for the file
                 */
                $filePath          = $fixedDirectory . $className . "." . $extension;
                $this->checkedPath = $filePath;

                $this->fireManagerEvent("loader:beforeCheckPath", $filePath);

                if ($this->requireFile($filePath)) {
                    if ($isDirectory) {
                        $this->addDebug("Directories: " . $filePath);
                    }

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks the registered namespaces to find the class. Includes the file if
     * found and returns true; false otherwise
     */
    private function autoloadCheckNamespaces(string $className): bool
    {
        $nsSeparator = "\\";
        $namespaces  = $this->namespaces;

        foreach ($namespaces as $prefix => $directories) {
            if (!str_starts_with($className, $prefix)) {
                continue;
            }

            /**
             * Append the namespace separator to the prefix
             */
            $prefix   = rtrim($prefix, $nsSeparator) . $nsSeparator;
            $fileName = substr($className, strlen($prefix));

            if ($this->autoloadCheckDirectories($directories, $fileName)) {
                $this->addDebug("Namespace: " . $prefix . " - " . $this->checkedPath);
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the directories is an array or a string and throws an exception
     * if not. It converts the string to an array and then traverses the array
     * to normalize the directories with the proper directory separator at the
     * end
     *
     * @return array<string, string>
     * @throws Exception
     */
    private function checkDirectories(mixed $directories, string $dirSeparator): array
    {
        if (!is_string($directories) && !is_array($directories)) {
            throw new Exception(
                "The directories parameter is not a string or array"
            );
        }

        if (is_string($directories)) {
            $directories = [$directories];
        }

        $results = [];
        foreach ($directories as $directory) {
            $directory = rtrim($directory, $dirSeparator) . $dirSeparator;

            $results[hash("sha256", $directory)] = $directory;
        }

        return $results;
    }
}
