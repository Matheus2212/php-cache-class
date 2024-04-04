<?php

/**
 *
 * 2021-06-06 -> Class created.
 * 2022-09-25 -> Fixed empty space regex and renamed Class
 * 
 * To do: Do a .css and .js files cache using the current document sourcecode, and try to get their content and compress.
 */

class Cache
{
        private static $cacheDir = "cache";
        private static $cacheLife = 60 * 60; // 1 hour
        private static $HTML = null;
        private static $path = null;
        private static $compressRegex = "/|\r|\n|[\s]{2,}/";

        /**
         * @return null
         * This method must be called at the start of the file. It will prepare everything for the caching
         */
        public static function init()
        {
                if ($_POST) {
                        return false;
                }
                ob_start();
                self::setPath();
                self::checkCache();
                self::checkDir();
        }

        /**
         * @return null
         * This method shaw be called at the end of the file. It is responsible to get the result of page rendering for the caching
         */
        public static function end()
        {
                if ($_POST) {
                        return false;
                }
                self::$HTML = ob_get_contents();
                self::compress();
                $aux = explode("?", $_SERVER['REQUEST_URI'])[0];
                $aux = implode("-", explode("/", $aux));
                $filename = ($aux == "-" ? "index" : $aux);
                if ($_GET) {
                        $filename .= "_" . sha1(http_build_query($_GET));
                }
                $path = self::$path . $filename . ".html";
                fopen($path, "w+");
                file_put_contents($path, "<!-- cached at: " . time() . " -->" . self::$HTML . "<!-- cached at: " . time() . " -->");
        }

        /**
         * @return null
         * Will set where to do cache, stripping GET params
         */
        private static function setPath()
        {
                self::$path = explode("?", self::$cacheDir . $_SERVER['REQUEST_URI'])[0];
        }

        /**
         * @return null
         * Will remove all empty white spaces in excess and line breaks
         */
        private static function compress()
        {
                self::$HTML = preg_replace(self::$compressRegex, "", self::$HTML);
        }

        /**
         * @param (string) $path 
         * Clear the cache on given $path
         */
        public static function clear($path = false)
        {
                if (!$path) {
                        $path = self::$cacheDir;
                }
                if (is_dir($path)) {
                        self::deleteDir($path);
                } else {
                        @unlink($path);
                }
        }

        /**
         * @param (string) $dirPath
         * IMPORTANT. Deletes the given directory
         */
        public static function deleteDir($dirPath)
        {
                if (!is_dir($dirPath)) {
                        throw new InvalidArgumentException("$dirPath must be a directory");
                }
                if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
                        $dirPath .= '/';
                }
                $files = glob($dirPath . '*', GLOB_MARK);
                foreach ($files as $file) {
                        if (is_dir($file)) {
                                self::deleteDir($file);
                        } else {
                                unlink($file);
                        }
                }
                rmdir($dirPath);
        }

        /**
         * @return null
         * Check if directory exists to generate cache
         */
        private static function checkDir()
        {
                $path = self::$path;
                $dirs = explode(DIRECTORY_SEPARATOR, $path);
                if (!is_dir($path)) {
                        mkdir($path, 0775, true);
                        $now = "";
                        foreach ($dirs as $dir) {
                                $now .= $dir . DIRECTORY_SEPARATOR;
                                chmod($now, 0775);
                        }
                }
        }

        /**
         * @return null
         * Checks if current cache has a valid lifetime
         */
        private static function checkCache()
        {
                $aux = explode("?", $_SERVER['REQUEST_URI'])[0];
                $aux = implode("-", explode("/", $aux));
                $filename = ($aux == "-" ? "index" : $aux);
                if ($_GET) {
                        $filename .= "_" . sha1(http_build_query($_GET));
                }
                $fullPath = self::$path . $filename . ".html";
                if (file_exists($fullPath)) {
                        $fileTime = filemtime($fullPath);
                        if (time() - $fileTime < self::$cacheLife) {
                                include($fullPath);
                                exit();
                        } else {
                                self::clear($fullPath);
                        }
                }
                $dirs = explode(DIRECTORY_SEPARATOR, $fullPath);
                $current = "";
                foreach ($dirs as $dir) {
                        $current .= $dir;
                        if (is_dir($current)) {
                                $fileTime = stat($current);
                                if ($fileTime) {
                                        if (time() - $fileTime['mtime'] < self::$cacheLife) {
                                                self::deleteDir($current);
                                        }
                                }
                                $current = $current . DIRECTORY_SEPARATOR;
                        }
                }
        }
}
