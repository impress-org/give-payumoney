const mix = require('laravel-mix');

mix.setPublicPath('assets/dist')
    .sourceMaps(false)

    .js('assets/js/admin/admin-settings.js', 'js/admin/admin-settings.js')

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
