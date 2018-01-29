# FreshFile
Simple, fast, standalone PHP library, that helps You to define, if any of your files were modified since last time You check - Is this file FRESH?

Script uses **filemtime()** function (with optional **clearstatcache()**) to get last modification time of file. All modification times are stored in one cache file (if You use multiple instances, multiple files will be created), so even when you use this library for houndreds of files, it will store their times in one file, and read&write of cache file will be done one time per request.

# Usage

You can create many instances as You want, or create one, and use it as dummy-singleton accessed by static method of class. As argument You must pass a valid cache filepath, where script can place it's metadata cache.

```php
// Create by object
$ff = new FreshFile($cacheFilepath);
// Create using factory
$ff = FreshFile::create($cacheFilepath);
```
Once created You can use Your object anywhere You pass it, or get the existend object from static call.
```php
// Get existent object from class
$ff = FreshFile::get();
```
In first solution You must pass this object anywhere You want use it. In second solution You have to create object one time, and You can use it anywhere You want without passing it as argument.

### Prevent save cache on object destroy

FreshFile saves metadata file when FreshFile object destroy (when PHP script/request ends), by default. But in some cases You may need to prevent this. Maybe there was some error in Your code and You won't save the current collected data in cache? To do this, pass the second argument as ***false*** to prevent this. From now, You must manually close the FreshFile and save the metadata collected in the request by using **close()** method.

```php
// Prevent default save on destroy object
$ff = new FreshFile($cacheFilepath, false);
$ff = FreshFile::create($cacheFilepath, false);

// ...some code...

// At the end, close the FreshFile and save metadata
$ff->close();
```

##### Remember!
If You have prevented save on destroy, You must always close the FreshFile object after Your script ends!

### Use case

You can check one or many files at one time. If You pass array of filepaths, and any of files will not be fresh (even 1 from 100), method returns true - which means file is fresh and some operations with this file need to be done.
```php
// One file
if($ff->isFresh($file))
    // Do something...
    
// Many files
if($ff->isFresh([ $file1, $file2, $file3 ]))
    // Do something...
```

If You check if the file is fresh, script automatically updates metadata for this file, and next time You ask object if the same file is fresh, script returns false - that means this file do not need to be updated. **Even when you call again line by line in the same script/request!!**
```php
var_dump($ff->isFresh($file)); // returns true
var_dump($ff->isFresh($file)); // returns false !!!
var_dump($ff->isFresh($file)); // returns false !!!
```

### Related files

You can define related files of one main file. This is usefull when You compile LESS or SCSS files with imports, create imports in configuration files, etc. Once passed related files, script will check also if these related files are fresh. If main file or any of related files will change, You will be noticed.

```php
$ff->setRelatedFiles($file, $relatedFiles);
$ff->isFresh($file);
```

You can also set related files, after first fresh-check. This can be usefull when You detect, if any of these files was modified, and You want to update related files for this file.

```php
if($ff->isFresh($file))
{
    // Do something with Config, LESS, SCSS...
    
    // After compiling files in library, You can get imported files from library, and set
    // them into FreshFile object. Next time these files will also be checked if were modified.
    // FreshFile remember related files between requests.
    $relatedFiles = $object->getImportedFiles();
    $ff->setRelatedFiles($file, $relatedFiles);
}
```

# Licence
This code is licensed under MIT License.
