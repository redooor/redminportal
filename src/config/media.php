<?php

return array(

    'allowed_extensions' => array(
        'mp3', 'm4a', 'wav', 'ogg', 'flac', 'aac',
        'mp4', 'm4v', 'mov', 'webm', 'avi', 'mkv',
        'pdf', 'zip', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'epub',
        'jpg', 'jpeg', 'png', 'gif',
    ),

    /*
     * Accepted content types for each extension. Office and zip formats may be
     * reported as a vendor type, a zip type or a generic binary stream.
     */
    'allowed_mimes' => array(
        'mp3' => array(
            'audio/mpeg', 'audio/mp3', 'audio/mpeg3', 'audio/x-mpeg', 'audio/x-mpeg-3',
            'audio/x-mp3', 'audio/mpg',
        ),
        'm4a' => array(
            'audio/mp4', 'audio/x-m4a', 'audio/m4a', 'application/mp4',
        ),
        'wav' => array(
            'audio/wav', 'audio/wave', 'audio/x-wav', 'audio/vnd.wave', 'application/octet-stream',
        ),
        'ogg' => array(
            'audio/ogg', 'application/ogg', 'audio/x-ogg', 'audio/vorbis', 'application/x-ogg',
        ),
        'flac' => array(
            'audio/flac', 'audio/x-flac', 'application/octet-stream',
        ),
        'aac' => array(
            'audio/aac', 'audio/x-aac', 'audio/aacp', 'audio/mp4', 'application/octet-stream',
        ),
        'mp4' => array(
            'video/mp4', 'audio/mp4', 'application/mp4', 'application/octet-stream',
        ),
        'm4v' => array(
            'video/x-m4v', 'video/mp4', 'application/mp4', 'application/octet-stream',
        ),
        'mov' => array(
            'video/quicktime', 'video/mov', 'application/octet-stream',
        ),
        'webm' => array(
            'video/webm', 'audio/webm', 'application/webm', 'application/octet-stream',
        ),
        'avi' => array(
            'video/x-msvideo', 'video/avi', 'video/msvideo', 'video/x-avi',
            'application/x-troff-msvideo', 'application/octet-stream',
        ),
        'mkv' => array(
            'video/x-matroska', 'audio/x-matroska', 'application/x-matroska',
            'video/matroska', 'application/octet-stream',
        ),
        'pdf' => array(
            'application/pdf', 'application/x-pdf', 'application/acrobat', 'applications/vnd.pdf',
            'text/pdf', 'text/x-pdf',
        ),
        'zip' => array(
            'application/zip', 'application/x-zip', 'application/x-zip-compressed',
            'multipart/x-zip', 'application/octet-stream',
        ),
        'doc' => array(
            'application/msword', 'application/vnd.ms-word', 'application/vnd.ms-office',
            'application/cdfv2', 'application/x-ole-storage', 'application/zip',
            'application/x-zip-compressed', 'application/octet-stream',
        ),
        'docx' => array(
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip', 'application/x-zip-compressed', 'application/octet-stream',
        ),
        'xls' => array(
            'application/vnd.ms-excel', 'application/msexcel', 'application/x-msexcel',
            'application/x-ms-excel', 'application/vnd.ms-office', 'application/cdfv2',
            'application/x-ole-storage', 'application/zip', 'application/x-zip-compressed',
            'application/octet-stream',
        ),
        'xlsx' => array(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip', 'application/x-zip-compressed', 'application/octet-stream',
        ),
        'ppt' => array(
            'application/vnd.ms-powerpoint', 'application/mspowerpoint', 'application/x-mspowerpoint',
            'application/vnd.ms-office', 'application/cdfv2', 'application/x-ole-storage',
            'application/zip', 'application/x-zip-compressed', 'application/octet-stream',
        ),
        'pptx' => array(
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/zip', 'application/x-zip-compressed', 'application/octet-stream',
        ),
        'txt' => array(
            'text/plain', 'application/text', 'text/x-text',
        ),
        'csv' => array(
            'text/csv', 'text/plain', 'application/csv', 'text/comma-separated-values',
            'application/vnd.ms-excel', 'application/octet-stream',
        ),
        'epub' => array(
            'application/epub+zip', 'application/epub', 'application/zip',
            'application/x-zip-compressed', 'application/octet-stream',
        ),
        'jpg' => array(
            'image/jpeg', 'image/pjpeg', 'image/jpg',
        ),
        'jpeg' => array(
            'image/jpeg', 'image/pjpeg', 'image/jpg',
        ),
        'png' => array(
            'image/png', 'image/x-png',
        ),
        'gif' => array(
            'image/gif',
        ),
    ),

    'max_size' => 1073741824,

);
