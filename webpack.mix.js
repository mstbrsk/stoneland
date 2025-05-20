const mix = require('laravel-mix');
const webpackConfig = require('./webpack.config');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your theme assets. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix
    .webpackConfig(webpackConfig)
    .options({
        processCssUrls: false,
        manifest: false,
        terser: {
            terserOptions: {
                mangle: false,
                compress: true,
                output: {
                    comments: false
                }
            },
        },
    })
    .setPublicPath('')
;

// Vendor Mixes
mix
    .copy('node_modules/jquery/dist/jquery.min.js', 'modules/system/assets/js/vendor/jquery.min.js')
    .copy('node_modules/vue-router/dist/vue-router.min.js', 'modules/system/assets/vendor/vue-router/vue.min.js')
    .copy('node_modules/bluebird/js/browser/bluebird.min.js', 'modules/system/assets/vendor/bluebird/bluebird.min.js')
    .copy('node_modules/sortablejs/Sortable.min.js', 'modules/backend/assets/vendor/sortablejs/sortable.js')
    .copy('node_modules/dropzone/dist/dropzone-min.js', 'modules/backend/assets/vendor/dropzone/dropzone.js')
    .copy('node_modules/js-cookie/dist/js.cookie.js', 'modules/backend/assets/vendor/js-cookie/js.cookie.js')


// Vue dev tools
if (!mix.inProduction()) {
    mix.copy('node_modules/vue/dist/vue.js', 'modules/system/assets/vendor/vue/vue.min.js');
}
else {
    mix.copy('node_modules/vue/dist/vue.min.js', 'modules/system/assets/vendor/vue/vue.min.js');
}

// Boostrap Mixes
// Boostrap Mixes
mix
    .setPublicPath('')
    .js('modules/backend/assets/vendor/bootstrap/bootstrap.js', 'modules/backend/assets/vendor/bootstrap/bootstrap.min.js')
    .sass('modules/backend/assets/vendor/bootstrap/bootstrap.scss', 'modules/backend/assets/vendor/bootstrap/bootstrap.css')

    .sass('modules/backend/assets/vendor/bootstrap/bootstrap-lite.scss', 'modules/backend/assets/vendor/bootstrap/bootstrap-lite.css')
    .sass('modules/backend/assets/vendor/bootstrap-icons/bootstrap-icons.scss', 'modules/backend/assets/vendor/bootstrap-icons/bootstrap-icons.css')
    .copy('node_modules/bootstrap-icons/font/fonts/', 'modules/backend/assets/vendor/bootstrap-icons/fonts/')
    .sass('themes/demo/assets/scss/abstracts/_variables.scss', 'themes/demo/assets/css/theme/variables.css')
    .sass('themes/demo/assets/scss/base/_global.scss', 'themes/demo/assets/css/theme/global.css')
    .sass('themes/demo/assets/scss/base/_reset.scss', 'themes/demo/assets/css/theme/reset.css')
    .sass('themes/demo/assets/scss/base/_typography.scss', 'themes/demo/assets/css/theme/typography.css')

    .sass('themes/demo/assets/scss/components/_buttons.scss', 'themes/demo/assets/css/theme/buttons.css')
    .sass('themes/demo/assets/scss/components/_forms.scss', 'themes/demo/assets/css/theme/forms.css')
    .sass('themes/demo/assets/scss/components/_preloader.scss', 'themes/demo/assets/css/theme/preloader.css')

    .sass('themes/demo/assets/scss/sections/_all-sections.scss', 'themes/demo/assets/css/theme/all-sections.css')
    .sass('themes/demo/assets/scss/layout/_banner.scss', 'themes/demo/assets/css/theme/banner.css')
    .sass('themes/demo/assets/scss/layout/_footer.scss', 'themes/demo/assets/css/theme/footer.css')
    .sass('themes/demo/assets/scss/layout/_header.scss', 'themes/demo/assets/css/theme/header.css')
    .sass('themes/demo/assets/scss/_responsive.scss', 'themes/demo/assets/css/theme/responsive.css')
    .sass('themes/demo/assets/scss/themes/_light-theme.scss', 'themes/demo/assets/css/theme/light-theme.css')

// CSS dosyalarını birleştirme
mix.styles([
    'themes/demo/assets/css/theme/variables.css',
    'themes/demo/assets/css/theme/header.css',
    'themes/demo/assets/css/theme/footer.css',
    'themes/demo/assets/css/theme/banner.css',

    'themes/demo/assets/css/theme/global.css',
    'themes/demo/assets/css/theme/reset.css',
    'themes/demo/assets/css/theme/typography.css',
    'themes/demo/assets/css/theme/buttons.css',
    'themes/demo/assets/css/theme/forms.css',
    'themes/demo/assets/css/theme/preloader.css',
    'themes/demo/assets/css/theme/all-sections.css',

    'themes/demo/assets/css/layouts/default.css',
    'themes/demo/assets/css/layouts/home.css',
    'themes/demo/assets/css/layouts/blog.css',
    'themes/demo/assets/css/layouts/wiki.css',
    'themes/demo/assets/css/controls/gallery-slider.css',
    'themes/demo/assets/css/controls/card-slider.css',
    'themes/demo/assets/css/controls/quantity-input.css',
    'themes/demo/assets/css/elements/text.css',
    'themes/demo/assets/css/elements/card.css',
    'themes/demo/assets/css/elements/nav.css',
    'themes/demo/assets/css/elements/navbar.css',
    'themes/demo/assets/css/elements/jumbotron.css',
    'themes/demo/assets/css/elements/pagination.css',
    'themes/demo/assets/css/elements/code.css',
    'themes/demo/assets/css/elements/buttons.css',
    'themes/demo/assets/css/elements/footer.css',
    'themes/demo/assets/css/elements/social-links.css',
    'themes/demo/assets/css/elements/user-panel.css',
    'themes/demo/assets/css/elements/lists.css',
    'themes/demo/assets/css/elements/form.css',
    'themes/demo/assets/css/elements/popover.css',
    'themes/demo/assets/css/elements/modals.css',
    'themes/demo/assets/css/elements/how-its-made.css'
], 'themes/demo/assets/css/main.css');


// Core Mixes
require('./webpack.helpers')(mix);
require('./modules/system/system.mix')(mix);
require('./modules/backend/backend.mix')(mix);
require('./modules/editor/editor.mix')(mix);
require('./modules/media/media.mix')(mix);
require('./modules/tailor/tailor.mix')(mix);
require('./modules/cms/cms.mix')(mix);
