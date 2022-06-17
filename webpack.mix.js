const mix = require('laravel-mix');

mix.setPublicPath('assets/dist')
    .sourceMaps(false)

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
