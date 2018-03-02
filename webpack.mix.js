let mix = require('laravel-mix');

mix.js('resources/assets/admin/js/app.js', 'public/admin/js')
   .sass('resources/assets/admin/sass/app.scss', 'public/admin/css');
