<?php 

 if (session_status() == PHP_SESSION_NONE) {
    session_start();
} 

$bootstrap_dependencies = [
    'bootstrap_css' =>          'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css',
    'bootstrap_js'  =>          'https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js',
    'jquery'        =>          'https://code.jquery.com/jquery-3.6.0.min.js',
    'jquery_slim'   =>          'https://code.jquery.com/jquery-3.6.1.slim.min.js',
    'fontawesome'   =>          'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css',
    'bootstrap_icon'  =>        'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css
',
];

