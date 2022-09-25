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
        private static $cacheLife = (60 * 60); // 1 hour
        private static $HTML = null;
        private static $path = null;
        private static $compressRegex = "/|\r|\n|[\s]{2,}/";

        /** This method must be called at the start of the file. It will prepare everything for the caching */
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

        /** This method shaw be called at the end of the file. It is responsible to get the result of page rendering for the caching */
        public static function end()
        {
                if ($_POST) {
                        return false;
                }
                self::$HTML = ob_get_contents();
                self::compress();
                $filename = "index";
                if ($_GET) {
                        $filename .= "_" . md5(http_build_query($_GET));
                }
                $path = self::$path . $filename . ".html";
                fopen($path, "w+");
                file_put_contents($path, "<!-- cached at: " . time() . " -->" . self::$HTML . "<!-- cached at: " . time() . " -->");
        }

        /** Will set where to cache */
        private static function setPath()
        {
                self::$path = explode("?", self::$cacheDir . $_SERVER['REQUEST_URI'])[0];
        }

        /** Will remove all empty white spaces in excess and line breaks */
        private static function compress()
        {
                self::$HTML = preg_replace(self::$compressRegex, "\s", self::$HTML);
        }

        /** Clear the cache for the given path */
        public static function clear($path = false)
        {
                if (!$path) {
                        $path = self::$cacheDir;
                }
                if (is_dir($path)) {
                        self::deleteDir($path);
                } else {
                        unlink($path);
                }
        }

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


        /** Check if dir structure exists */
        private static function checkDir()
        {
                $path = self::$path;
                $dirs = explode(DIRECTORY_SEPARATOR, $path);
                if (!is_dir($path)) {
                        mkdir($path, 0777, true);
                        $now = "";
                        foreach ($dirs as $dir) {
                                $now .= $dir . DIRECTORY_SEPARATOR;
                                chmod($now, 0777);
                        }
                }
        }

        /** Check if current cache has a valid lifetime */
        private static function checkCache()
        {
                $filename = "index";
                if ($_GET) {
                        $filename .= "_" . md5(http_build_query($_GET));
                }
                $fullPath = self::$path . $filename . ".html";
                if (file_exists($fullPath)) {
                        $fileTime = filemtime($fullPath);
                        if ($fileTime + self::$cacheLife > time()) {
                                //$contents = file_get_contents($fullPath);
                                //echo $contents;
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
                                        if ($fileTime['mtime'] + self::$cacheLife < time()) {
                                                self::deleteDir($current);
                                        }
                                }
                                $current = $current . DIRECTORY_SEPARATOR;
                        }
                }
        }
}
