# FreshFile
Simple, fast, standalone PHP lib, that helps You to define, if any of your files were modified since last time You check - Is this file FRESH?

Script uses **filemtime()** function (with optional **clearstatcache()**) to get last modification time of file. All modification times are stored in one cache file (if You use multiple instances, multiple files will be created), so even when you use this library for houndreds of files, it will store their times in one file, and read&write will be done one time per request.

## Usage

You can create many instances as You want, or create one, and use it as dummy-singleton accessed by static method of class. As argument You must pass a valid cache directory path, where script can place it's metadata cache file.

```php
// Create by object
$ff = new FreshFile($cacheDirectory);
// Create using factory
$ff = FreshFile::create($cacheDirectory);
```
Once created You can use Your object anywhere You pass it, or get the existend object from static call.
```php
// Get existent object from class
$ff = FreshFile::get();
```
In first solution You must pass this object anywhere You want use it. In second solution You have to create object one time, and You can use it anywhere You want without passing it as argument.

### Use case

You can check one or many files at one time. If You pass array of filepaths, and any of files will not be fresh (even 1 from 100), method returns false - which means file is not fresh and need to be updated.
```php
// One file
if($ff->isFresh($file) === false)
    // Do something...
    
// Many files
if($ff->isFresh([ $file1, $file2, $file3 ]) === false)
    // Do something...
```

If You check if the file is fresh, script automatically updates metadata for this file, and next time You ask object if the same file is fresh, script teturns true - that means this file is fresh. **Even when you call again line by line in the same script/request!!**
```php
var_dump($ff->isFresh($file)); // returns false
var_dump($ff->isFresh($file)); // returns true !!!
var_dump($ff->isFresh($file)); // returns true !!!
```

# Licence
This code is licensed under MIT License.
