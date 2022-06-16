const mix = require('laravel-mix');

mix.setPublicPath('assets/dist')
    .sourceMaps(false)

    .js('assets/src/js/admin/admin-settings.js', 'js/admin/admin-settings.js')

mix.options({
    terser: {
        extractComments: (astNode, comment) => false,
        terserOptions: {
            format: {
                comments: false,
            },
        },
    },
});
