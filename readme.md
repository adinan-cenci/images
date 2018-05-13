# Images

A small library to generate thumbnails and do a bit of editing.  
Why? Because the gd library is a pain to deal with.


## Thumbnails

Here, lets create a 200 x 200 image and fill it with an image.

<img src="example-original.jpeg" alt="original" width="300">

```php
$file       = new File('images/my-photo.jpg');
$thumbnail  = new Image(200, 200);

$thumbnail->fillWith($file);

header('Content-type: '.$file->mime);
$thumbnail->imageJpg();
```

<img src="example-fill.jpeg" alt="original" width="200">

Need it to fit the entire image neately inside the thumbnail?  
Use ::fit instead:

```php
$thumbnail->fit($file);

header('Content-type: '.$file->mime);
$thumbnail->imageJpg();
```

<img src="example-fit.jpeg" alt="original" width="200">