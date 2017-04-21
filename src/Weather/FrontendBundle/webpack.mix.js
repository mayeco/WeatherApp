const { mix } = require('laravel-mix');

mix
    .setPublicPath('Resources/public/')
    .copy('Resources/assets/images/','Resources/public/images')
    .js('Resources/assets/js/app.js', 'Resources/public/js')
    .sass('Resources/assets/sass/app.scss', 'Resources/public/css')
    .version()
;



const { mix } = require('laravel-mix');

mix
    .setPublicPath('Resources/public')

    .copy('Resources/assets/images/','Resources/public/images')
    .js('Resources/assets/js/app.js', 'Resources/public/js')
    .sass('Resources/assets/sass/app.scss', 'Resources/public/css')
;

if(mix.config.inProduction) {
    mix
        .version()
    ;
} else {
    mix
        .browserSync('app.dev')
    ;
}
