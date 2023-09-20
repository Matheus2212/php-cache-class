# php-cache-class

This class is responsible to do simple caching on websites. It gets all HTML output and store it in an .html file on the provided folder.

---

### Cache::init()

This method must be called at the start of the file. It will prepare everything for caching

### Cache::end()

This method shall be called at the end of the file. It is responsible to get the result of page rendering for the caching

### Cache::clear($path) $path = string

Clear the cache for the given path
