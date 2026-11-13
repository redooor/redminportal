<?php

return array(

    'library'     => 'gd',
    'upload_dir'  => 'assets/img',
    'upload_path' => public_path() . '/assets/img/',
    'quality'     => 85,
    // JPEG quality used when an uploaded original is re-encoded.
    'upload_quality' => 90,
    // Maximum decoded pixels (width * height) accepted for an uploaded image.
    'max_pixels' => 40000000,
 
    'dimensions' => array(
        'thumb'  => array(100, 100, true,  80),
        'medium' => array(600, 400, false, 90),
        'large'  => array(800, 600, false, 90),
    ),
    
);
