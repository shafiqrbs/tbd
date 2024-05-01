const mix = require('laravel-mix');
const fs = require('fs');
const path = require('path');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

const moduleFolder = './Modules';

const dirs = p => fs.readdirSync(p).filter(f => fs.statSync(path.resolve(p,f)).isDirectory());

// Get the available modules return as array
let modules = dirs(moduleFolder);

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/scss/app.scss', 'public/css');

// Loop available modules
modules.forEach(function(mod){
    mix.js(__dirname  + "/Modules/" + mod + "/Resources/assets/js/app.js", 'public/js');
    mix.sass(__dirname  + "/Modules/" + mod + "/Resources/assets/sass/app.scss", 'public/css');
});

// Let's parse again to fix the newline every module for css
mix.styles(['public/css/app.css'], 'public/css/app.css');
